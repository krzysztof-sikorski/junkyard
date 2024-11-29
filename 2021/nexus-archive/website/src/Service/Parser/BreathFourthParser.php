<?php

declare(strict_types=1);

namespace App\Service\Parser;

use App\Contract\Entity\LeaderboardTypes;
use App\Contract\Entity\Nexus\GamePeriodIdEnum;
use App\Contract\Entity\Nexus\LeaderboardInterface;
use App\Contract\Service\Parser\ParserError;
use App\Contract\Service\Parser\ParserInterface;
use App\Contract\Service\Parser\ParserResultInterface;
use App\Doctrine\Entity\Nexus\GamePeriod;
use App\Doctrine\Entity\PageView;
use App\DTO\Nexus\Leaderboard;
use App\DTO\Nexus\Leaderboard\Entry;
use App\DTO\ParserResult;
use App\Service\Repository\Nexus\GamePeriodRepository;
use DateTimeInterface;
use Symfony\Component\DomCrawler\Crawler;

use function array_key_exists;
use function iconv;
use function in_array;
use function intval;
use function is_string;
use function mb_convert_case;
use function mt_rand;
use function parse_str;
use function parse_url;
use function preg_match;
use function sprintf;
use function str_replace;
use function substr;

use const MB_CASE_LOWER;
use const PHP_URL_PATH;
use const PHP_URL_QUERY;

final class BreathFourthParser implements ParserInterface
{
    private const EXPECTED_PATH = '/modules.php';
    private const MODULE_ARG_NAME = 'name';
    private const MODULE_ARG_VALUE_GAME = 'Game';
    private const ACTION_ARG_NAME = 'op';
    private const ACTION_ARG_VALUE_USE_ITEM = 'useitem';

    private const REGEXP_META_CHARSET_TAG = '/<META[^<>]+(?P<attribute>charset=[^\s"\']+)[^<>]*>/i';
    private const REGEXP_META_CHARSET_ATTRIBUTE = '/^charset=(?P<value>.+)/i';
    private const REGEXP_MESSAGES_NEWSPAPER_USE = '/^\\s*-\\s+(\(\d+\\s+times\)\\s*)?You read the newspaper/i';
    private const REGEXP_LEADERBOARD_TABLE_HEADER = '/^(?P<name>.+)\s+\((?P<type>[^()]+)\)$/i';
    private const REGEXP_LEADERBOARD_ENTRY_HEADER = '/^(?P<position>\d+)\.\)\\s*(?P<characterName>.+)$/';

    private const KNOWN_ITEM_USE_ERRORS = [
        'You do not have enough action points to act.',
        'Sorry, you do not own this item!',
    ];

    private GamePeriod $gamePeriod;
    private DateTimeInterface $breathEnd;

    public function __construct(GamePeriodRepository $gamePeriodRepository)
    {
        $gamePeriod = $gamePeriodRepository->findById(id: GamePeriodIdEnum::BREATH_4);
        if (null === $gamePeriod) {
            throw new \RuntimeException(message: 'Could not find B4 game period in database!');
        }
        $this->gamePeriod = $gamePeriod;
        $this->breathEnd = $this->gamePeriod->getCompletedAt();
    }

    public function supports(PageView $pageView): bool
    {
        return $this->breathEnd >= $pageView->getRequestStartedAt();
    }

    public function parse(PageView $pageView): ParserResultInterface
    {
        $result = new ParserResult();
        $result->setGamePeriod(gamePeriod: $this->gamePeriod);

        if ($this->isNotItemUse(pageView: $pageView)) {
            return $result;
        }

        $responseBody = $pageView->getResponseBody();
        if (empty($responseBody)) {
            throw new ParserError(message: 'Stored response body is empty');
        }
        $encoding = $this->detectEncoding(responseBody: $responseBody);
        $responseBody = iconv(from_encoding: $encoding, to_encoding: 'UTF-8', string: $responseBody);

        $leaderboard = $this->getLeaderboardFromResponseBody(responseBody: $responseBody);

        $result->setLeaderboard(leaderboard: $leaderboard);

        return $result;
    }

    private function isNotItemUse(PageView $pageView): bool
    {
        // extract path and query params from URL
        $url = $pageView->getUrl();
        $path = parse_url(url: $url, component: PHP_URL_PATH);
        $queryStr = parse_url(url: $url, component: PHP_URL_QUERY);
        $queryParams = [];
        if (is_string(value: $queryStr)) {
            parse_str(string: $queryStr, result: $queryParams);
        }

        // check URL path
        if (self::EXPECTED_PATH !== $path) {
            return true;
        }

        // check module name
        if (
            false === array_key_exists(key: self::MODULE_ARG_NAME, array: $queryParams)
            || self::MODULE_ARG_VALUE_GAME !== $queryParams[self::MODULE_ARG_NAME]
        ) {
            return true;
        }

        // check action type
        if (
            false === array_key_exists(key: self::ACTION_ARG_NAME, array: $queryParams)
            || self::ACTION_ARG_VALUE_USE_ITEM !== $queryParams[self::ACTION_ARG_NAME]
        ) {
            return true;
        }

        return false;
    }

    private function detectEncoding(string $responseBody): string
    {
        $htmlSample = substr(string: $responseBody, offset: 0, length: 512);

        // find charset declaration
        $matches = [];
        preg_match(pattern: self::REGEXP_META_CHARSET_TAG, subject: $htmlSample, matches: $matches);
        if (false === array_key_exists(key: 'attribute', array: $matches)) {
            throw new ParserError(message: 'Could not find META CHARSET element in page source');
        }
        $charsetAttribute = $matches['attribute'];

        // find charset value
        $matches = [];
        preg_match(pattern: self::REGEXP_META_CHARSET_ATTRIBUTE, subject: $charsetAttribute, matches: $matches);
        if (false === array_key_exists(key: 'value', array: $matches)) {
            throw new ParserError(message: 'Could not get CHARSET value from META CHARSET element');
        }

        return $matches['value'];
    }

    private function getLeaderboardFromResponseBody(string $responseBody): ?Leaderboard
    {
        $crawler = new Crawler(node: $responseBody);

        // search for messages section
        $messagesCrawler = $crawler->filter(selector: '#Messages');
        if (0 === $messagesCrawler->count()) {
            return null;
        }

        // check if last message is newspaper use
        $messages = $messagesCrawler->html();
        $pregMatchResult = preg_match(pattern: self::REGEXP_MESSAGES_NEWSPAPER_USE, subject: $messages);
        if (1 !== $pregMatchResult) {
            return null;
        }

        // search for errors section (for some reason it is also sometimes reused to show item use results)
        $errorsCrawler = $crawler->filter(selector: '#Errors');
        if (0 === $errorsCrawler->count()) {
            return null;
        }

        // check if errors section contain an actual error message
        if (in_array(needle: $errorsCrawler->text(), haystack: self::KNOWN_ITEM_USE_ERRORS, strict: true)) {
            return null;
        }

        // search for use result section (for some reason labelled "Errors") and leaderboard table inside
        $leaderboardTableCrawler = $crawler->filter(selector: '#Errors table')->eq(position: 1);
        if (0 === $leaderboardTableCrawler->count()) {
            throw new ParserError(message: 'Could not find leaderboard table, despite being newspaper use page');
        }

        // parse table headers
        $leaderboardName = '';
        $leaderboardType = '';
        $scoreLabel = '';
        $tableHeadersCrawler = $leaderboardTableCrawler->filter(selector: 'th');
        if (3 > $tableHeadersCrawler->count()) {
            throw new ParserError(message: 'Missing headers in leaderboard table');
        }
        foreach ($tableHeadersCrawler as $nodeIndex => $node) {
            if (0 === $nodeIndex) {
                $leaderboardHeader = $node->textContent;
                $matches = [];
                preg_match(
                    pattern: self::REGEXP_LEADERBOARD_TABLE_HEADER,
                    subject: $leaderboardHeader,
                    matches: $matches
                );
                if (false === isset($matches['name'], $matches['type'])) {
                    throw new ParserError(message: 'Could not find name and type in leaderboard header');
                }
                $leaderboardName = $matches['name'];
                $leaderboardType = mb_convert_case(string: $matches['type'], mode: MB_CASE_LOWER, encoding: 'UTF-8');
            } elseif (2 === $nodeIndex) {
                $scoreLabel = $node->textContent;
            }
        }

        // parse entry rows
        $entryRows = [];
        $tableRowsCrawler = $leaderboardTableCrawler->filter(selector: 'tr');
        $tableRowsCount = $tableRowsCrawler->count();
        if (3 > $tableHeadersCrawler->count()) {
            throw new ParserError(message: 'Missing entry rows in leaderboard table');
        }
        for ($rowIndex = 2; $rowIndex < $tableRowsCount; $rowIndex++) {
            $rowCellsCrawler = $tableRowsCrawler->eq(position: $rowIndex)->filter(selector: 'td');
            if (2 > $rowCellsCrawler->count()) {
                throw new ParserError(message: 'Not enough cells in entry row');
            }
            $entryHeader = $rowCellsCrawler->getNode(position: 0)->textContent;
            $entryScoreStr = $rowCellsCrawler->getNode(position: 1)->textContent;
            $matches = [];
            preg_match(pattern: self::REGEXP_LEADERBOARD_ENTRY_HEADER, subject: $entryHeader, matches: $matches);
            if (false === isset($matches['position'], $matches['characterName'])) {
                throw new ParserError(message: 'Could not find name and type in leaderboard header');
            }
            $entryRows[] = [
                'position' => intval(value: $matches['position']),
                'characterName' => $matches['characterName'],
                'score' => $this->parseScoreString(value: $entryScoreStr),
            ];
        }

        // build leaderboard instance
        $leaderboard = new Leaderboard();
        $leaderboard->setName(name: $leaderboardName);
        $leaderboard->setType(type: $leaderboardType);
        $leaderboard->setScoreLabel(scoreLabel: $scoreLabel);
        $leaderboardEntries = $leaderboard->getEntries();
        foreach ($entryRows as $entryRow) {
            $entry = new Entry();
            $entry->setCharacterName(characterName: $entryRow['characterName']);
            $entry->setScore(value: $entryRow['score']);
            $leaderboardEntries[$entryRow['position']] = $entry;
        }

        return $leaderboard;
    }

    private function parseScoreString(string $value): int
    {
        return intval(value: str_replace(search: [',', '.'], replace: '', subject: $value));
    }
}
