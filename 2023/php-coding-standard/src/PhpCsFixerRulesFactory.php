<?php
/**
 * My personal coding standard
 *
 * @author Krzysztof Sikorski
 * @copyright 2023 Krzysztof Sikorski
 */

declare(strict_types=1);

namespace KrzysztofSikorski\CodingStandard;

final class PhpCsFixerRulesFactory
{
    private const DEFAULT_RULES = [
        // rule sets
        '@PER-CS1.0' => true,
        '@PER-CS1.0:risky' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,

        // "Alias" rules
        'array_push' => true,
        'backtick_to_shell_exec' => true,
        'ereg_to_preg' => true,
        'mb_str_functions' => true,
        'no_alias_language_construct_call' => true,
        'no_mixed_echo_print' => true,
        'set_type_to_cast' => true,

        // "Array Notation" rules
        'no_multiline_whitespace_around_double_arrow' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
        'yield_from_array_to_yields' => true,

        // "Basic" rules
        'curly_braces_position' => [
            'allow_single_line_empty_anonymous_classes' => true,
            'allow_single_line_anonymous_functions' => true,
        ],
        'no_trailing_comma_in_singleline' => true,
        'psr_autoloading' => true,
        'single_line_empty_body' => true,

        // "Casing" rules
        'class_reference_name_casing' => true,
        'integer_literal_case' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,

        // "Cast Notation" rules
        'cast_spaces' => true,
        'modernize_types_casting' => true,
        'no_short_bool_cast' => true,

        // "Class Notation" rules
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'only_if_meta',
                'method' => 'one',
                'property' => 'only_if_meta',
                'trait_import' => 'none',
                'case' => 'none',
            ],
        ],
        'final_class' => true,
        'final_internal_class' => false,
        'final_public_method_for_abstract_class' => true,
        'no_null_property_initialization' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant',
                'property',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_abstract',
                'method',
            ],
        ],
        'ordered_interfaces' => true,
        'ordered_traits' => true,
        'ordered_types' => true,
        'protected_to_private' => true,
        'self_accessor' => true,
        'self_static_accessor' => true,
        'single_class_element_per_statement' => true,

        // "Class Usage" rules
        'date_time_immutable' => true,

        // "Comment" rules
        'comment_to_phpdoc' => true,
        'header_comment' => false,
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'single_line_comment_spacing' => true,
        'single_line_comment_style' => true,

        // "Constant Notation" rules
        'native_constant_invocation' => true,

        // "Control Structure" rules
        'empty_loop_body' => ['style' => 'braces'],
        'empty_loop_condition' => true,
        'include' => true,
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_unneeded_control_parentheses' => [
            'statements' => [
                'break',
                'clone',
                'continue',
                'echo_print',
                'negative_instanceof',
                'others',
                'return',
                'switch_case',
                'yield',
                'yield_from',
            ],
        ],
        'no_unneeded_curly_braces' => ['namespaces' => true],
        'no_useless_else' => true,
        'simplified_if_return' => true,
        'switch_continue_to_break' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arguments', 'arrays', 'match', 'parameters'],
        ],
        'yoda_style' => ['always_move_variable' => true],

        // "Function Notation" rules
        'date_time_create_from_format_call' => true,
        'fopen_flag_order' => true,
        'fopen_flags' => true,
        'lambda_not_used_import' => true,
        'native_function_invocation' => ['include' => ['@all']],
        'no_useless_sprintf' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'regular_callable_call' => true,
        'single_line_throw' => false,
        'static_lambda' => true,

        // "Import" rules
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => ['import_constants' => true, 'import_functions' => true, 'import_classes' => true],
        'group_import' => false,
        'no_unneeded_import_alias' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const']],
        'single_import_per_statement' => true,

        // "Language Construct" rules
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_parentheses' => true,
        'dir_constant' => true,
        'error_suppression' => ['mute_deprecation_error' => false, 'noise_remaining_usages' => true],
        'explicit_indirect_variable' => true,
        'function_to_constant' => true,
        'is_null' => true,
        'no_unset_on_property' => true,
        'nullable_type_declaration' => ['syntax' => 'union'],
        'single_space_around_construct' => true,

        // "Namespace Notation" rules
        'no_leading_namespace_whitespace' => true,

        // "Naming" rules
        'no_homoglyph_names' => true,

        // "Operator" rules
        'binary_operator_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => true,
        'logical_operators' => true,
        'no_useless_concat_operator' => ['juggle_simple_strings' => true],
        'no_useless_nullsafe_operator' => true,
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => true,
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'ternary_to_elvis_operator' => true,
        'unary_operator_spaces' => true,

        // "PHP Tag" rules
        'echo_tag_syntax' => true,
        'linebreak_after_opening_tag' => true,

        // "PHPUnit" rules
        'php_unit_construct' => true,
        'php_unit_data_provider_name' => false,
        'php_unit_data_provider_return_type' => true,
        'php_unit_fqcn_annotation' => true,
        'php_unit_internal_class' => false,
        'php_unit_method_casing' => true,
        'php_unit_mock_short_will_return' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_size_class' => false,
        'php_unit_strict' => true,
        'php_unit_test_annotation' => false,
        'php_unit_test_case_static_method_calls' => true,
        'php_unit_test_class_requires_covers' => true,

        // "PHPDoc" rules
        'align_multiline_comment' => true,
        'general_phpdoc_annotation_remove' => false,
        'general_phpdoc_tag_rename' => false,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_add_missing_param_annotation' => false,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_line_span' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order_by_value' => [
            'annotations' => [
                'author',
                'covers',
                'coversNothing',
                'dataProvider',
                'depends',
                'group',
                'internal',
                'method',
                'mixin',
                'property',
                'property-read',
                'property-write',
                'requires',
                'throws',
                'uses',
            ],
        ],
        'phpdoc_order' => true,
        'phpdoc_param_order' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_casing' => true,
        'phpdoc_tag_type' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,

        // "Return Notation" rules
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,

        // "Semicolon" rules
        'multiline_whitespace_before_semicolons' => true,
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],

        // "Strict" rules
        'strict_comparison' => true,
        'strict_param' => true,

        // "String Notation" rules
        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'single_quote' => true,
        'string_length_to_empty' => true,
        'string_line_ending' => true,

        // "Whitespace" rules
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'case',
                'continue',
                'declare',
                'default',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                'phpdoc',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
                'yield_from',
            ],
        ],
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'attribute',
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ],
        ],
        'no_spaces_around_offset' => true,
        'type_declaration_spaces' => true,
        'types_spaces' => ['space' => 'single'],
    ];

    public static function create(null | string $header): array
    {
        $rules = self::DEFAULT_RULES;

        if (null !== $header) {
            $rules['header_comment'] = [
                'header' => $header,
                'comment_type' => 'PHPDoc',
                'location' => 'after_open',
                'separate' => 'bottom',
            ];
        }

        return $rules;
    }
}
