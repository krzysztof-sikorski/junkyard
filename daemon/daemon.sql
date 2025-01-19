CREATE TABLE battles (
  battle_id int NOT NULL AUTO_INCREMENT,
  rollover_id smallint NOT NULL,
  location_id varchar(32) NOT NULL,
  "type" enum('caern','boss') NOT NULL,
  combat_log text NOT NULL,
  PRIMARY KEY (battle_id),
  KEY rollover_id (rollover_id,battle_id)
);


CREATE TABLE characters (
  character_id int NOT NULL AUTO_INCREMENT,
  player_id int DEFAULT NULL,
  "name" varchar(255) NOT NULL,
  gender enum('f','m','n') NOT NULL,
  date_created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_action datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  show_player tinyint(1) NOT NULL DEFAULT 0,
  last_mail_id int NOT NULL DEFAULT 0,
  clan_id varchar(8) DEFAULT NULL,
  avatar_url tinytext,
  quote text,
  description text,
  PRIMARY KEY (character_id),
  UNIQUE KEY "name" ("name"),
  KEY clan (clan_id,character_id),
  KEY date_created (date_created,character_id),
  KEY player_id (player_id,character_id),
  KEY last_action (last_action)
);


CREATE TABLE character_data (
  character_id int NOT NULL,
  location_id varchar(32) DEFAULT NULL COMMENT 'current location',
  faction_id varchar(32) DEFAULT NULL,
  faction_points smallint NOT NULL DEFAULT 0,
  rank_id tinyint DEFAULT NULL,
  turns smallint NOT NULL DEFAULT 30,
  gold_purse int NOT NULL DEFAULT 0,
  gold_bank int NOT NULL DEFAULT 0,
  "level" smallint NOT NULL DEFAULT 0,
  xp_free int NOT NULL DEFAULT 29,
  xp_used int NOT NULL DEFAULT 0,
  deaths int NOT NULL DEFAULT 0,
  health smallint NOT NULL DEFAULT 0,
  health_max smallint NOT NULL DEFAULT 70,
  mana smallint NOT NULL DEFAULT 0,
  mana_max smallint NOT NULL DEFAULT 35,
  mana_regen smallint NOT NULL DEFAULT 1,
  a_str smallint NOT NULL DEFAULT 7,
  a_dex smallint NOT NULL DEFAULT 7,
  a_vit smallint NOT NULL DEFAULT 7,
  a_pwr smallint NOT NULL DEFAULT 7,
  a_wil smallint NOT NULL DEFAULT 7,
  s_pstr smallint NOT NULL DEFAULT 0,
  s_patk smallint NOT NULL DEFAULT 0,
  s_pdef smallint NOT NULL DEFAULT 0,
  s_pres smallint NOT NULL DEFAULT 0,
  s_preg smallint NOT NULL DEFAULT 0,
  s_mstr smallint NOT NULL DEFAULT 0,
  s_matk smallint NOT NULL DEFAULT 0,
  s_mdef smallint NOT NULL DEFAULT 0,
  s_mres smallint NOT NULL DEFAULT 0,
  s_mreg smallint NOT NULL DEFAULT 0,
  sp_scout tinyint NOT NULL DEFAULT 0,
  sp_identify tinyint NOT NULL DEFAULT 0,
  sp_vchar tinyint NOT NULL DEFAULT 0,
  sp_vmonster tinyint NOT NULL DEFAULT 0,
  sp_vitem tinyint NOT NULL DEFAULT 0,
  combat_unit_id varchar(32) DEFAULT NULL,
  location_event text COMMENT 'event parameters',
  PRIMARY KEY (character_id),
  KEY "level" ("level",character_id),
  KEY xp_used (xp_used,character_id),
  KEY faction_id (faction_id,character_id),
  KEY location_id (location_id,character_id)
);


CREATE TABLE character_missions (
  character_id int NOT NULL,
  rollover_id int NOT NULL,
  service_id varchar(32) NOT NULL,
  "type" enum('monster','item') NOT NULL,
  params varchar(32) NOT NULL,
  progress enum('active','completed','rewarded') NOT NULL DEFAULT 'active',
  PRIMARY KEY (character_id,rollover_id)
);


CREATE TABLE character_regions (
  character_id int NOT NULL,
  region_id varchar(32) NOT NULL,
  PRIMARY KEY (character_id,region_id)
);


CREATE TABLE character_statistics (
  character_id int NOT NULL,
  missions smallint NOT NULL DEFAULT 0,
  duel_wins smallint NOT NULL DEFAULT 0,
  duel_losses smallint NOT NULL DEFAULT 0,
  kills_mob1 smallint NOT NULL DEFAULT 0,
  kills_mob2 smallint NOT NULL DEFAULT 0,
  kills_mob3 smallint NOT NULL DEFAULT 0,
  kills_mob4 smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (character_id),
  KEY duel_wins (duel_wins,character_id),
  KEY duel_losses (duel_losses,character_id)
);


CREATE TABLE character_titles (
  character_id int NOT NULL,
  title_id varchar(32) NOT NULL,
  PRIMARY KEY (character_id,title_id)
);


CREATE TABLE chat (
  message_id int NOT NULL AUTO_INCREMENT,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  channel_id varchar(255) NOT NULL,
  sender_id int DEFAULT NULL,
  content text NOT NULL,
  PRIMARY KEY (message_id),
  KEY sort (channel_id,message_id)
);


CREATE TABLE clans (
  clan_id varchar(8) NOT NULL,
  "name" varchar(255) NOT NULL,
  date_created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  leader_id int DEFAULT NULL,
  description text,
  PRIMARY KEY (clan_id),
  UNIQUE KEY "name" ("name")
);


CREATE TABLE clan_invitations (
  clan_id varchar(32) NOT NULL,
  character_id int NOT NULL,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  description text,
  PRIMARY KEY (clan_id,character_id),
  KEY search (character_id,clan_id)
);


CREATE TABLE combat_units (
  combat_unit_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  faction_id varchar(32) DEFAULT NULL,
  health int NOT NULL DEFAULT 70,
  health_max int NOT NULL DEFAULT 70,
  str1 smallint NOT NULL DEFAULT 7,
  atk1 smallint NOT NULL DEFAULT 7,
  type1 enum('p','m') DEFAULT 'p',
  count1 smallint NOT NULL DEFAULT 1,
  sp1_type varchar(32) DEFAULT NULL,
  sp1_param smallint DEFAULT NULL,
  str2 smallint NOT NULL DEFAULT 7,
  atk2 smallint NOT NULL DEFAULT 7,
  type2 enum('p','m') DEFAULT NULL,
  count2 smallint NOT NULL DEFAULT 0,
  sp2_type varchar(32) DEFAULT NULL,
  sp2_param smallint DEFAULT NULL,
  pdef smallint NOT NULL DEFAULT 7,
  pres smallint NOT NULL DEFAULT 7,
  mdef smallint NOT NULL DEFAULT 7,
  mres smallint NOT NULL DEFAULT 7,
  speed smallint NOT NULL DEFAULT 7,
  armor smallint NOT NULL DEFAULT 0,
  armor_sp_type varchar(32) DEFAULT NULL,
  armor_sp_param smallint DEFAULT NULL,
  regen float NOT NULL DEFAULT 0,
  PRIMARY KEY (combat_unit_id)
);


CREATE TABLE duels (
  duel_id int NOT NULL AUTO_INCREMENT,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  rollover_id smallint DEFAULT NULL,
  attacker_id int NOT NULL,
  defender_id int NOT NULL,
  "type" enum('normal','arena') NOT NULL,
  winner enum('a','b') DEFAULT NULL,
  combat_log text,
  PRIMARY KEY (duel_id)
);


CREATE TABLE "events" (
  event_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  handle varchar(255) DEFAULT NULL,
  description text,
  PRIMARY KEY (event_id)
);


CREATE TABLE factions (
  faction_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  power smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (faction_id)
);


CREATE TABLE faction_ranks (
  faction_id varchar(32) NOT NULL,
  rank_id tinyint NOT NULL,
  min_points smallint NOT NULL DEFAULT 1,
  title_id varchar(32) DEFAULT NULL,
  PRIMARY KEY (faction_id,rank_id)
);


CREATE TABLE inventory (
  inventory_id int NOT NULL AUTO_INCREMENT,
  character_id int NOT NULL,
  item_id varchar(32) NOT NULL,
  "status" enum('inventory','storage') NOT NULL DEFAULT 'inventory',
  flags set('bound','identified') NOT NULL,
  equipped enum('hand_a','hand_b','armor','helmet','gloves','boots','pendant','accesory_a','accesory_b') DEFAULT NULL,
  PRIMARY KEY (inventory_id),
  KEY sort (character_id,"status")
);


CREATE TABLE items (
  item_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  "type" enum('weapon1h','weapon2h','armor','helmet','gloves','boots','pendant','accesory','item') NOT NULL DEFAULT 'item',
  "value" int NOT NULL DEFAULT 0,
  suggested_value float NOT NULL DEFAULT 0,
  damage_type enum('p','m') DEFAULT NULL,
  special_type varchar(32) DEFAULT NULL,
  special_param varchar(255) DEFAULT NULL,
  pstr_p smallint NOT NULL DEFAULT 0,
  pstr_c smallint NOT NULL DEFAULT 0,
  patk_p smallint NOT NULL DEFAULT 0,
  patk_c smallint NOT NULL DEFAULT 0,
  pdef_p smallint NOT NULL DEFAULT 0,
  pdef_c smallint NOT NULL DEFAULT 0,
  pres_p smallint NOT NULL DEFAULT 0,
  pres_c smallint NOT NULL DEFAULT 0,
  mstr_p smallint NOT NULL DEFAULT 0,
  mstr_c smallint NOT NULL DEFAULT 0,
  matk_p smallint NOT NULL DEFAULT 0,
  matk_c smallint NOT NULL DEFAULT 0,
  mdef_p smallint NOT NULL DEFAULT 0,
  mdef_c smallint NOT NULL DEFAULT 0,
  mres_p smallint NOT NULL DEFAULT 0,
  mres_c smallint NOT NULL DEFAULT 0,
  armor smallint NOT NULL DEFAULT 0,
  speed smallint NOT NULL DEFAULT 0,
  regen smallint NOT NULL DEFAULT 0,
  description text,
  PRIMARY KEY (item_id)
);


CREATE TABLE item_specials (
  special_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  handle varchar(255) DEFAULT NULL,
  description text,
  PRIMARY KEY (special_id)
);


CREATE TABLE item_templates (
  id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  pstr_p_p smallint NOT NULL DEFAULT 1,
  pstr_p_m smallint NOT NULL DEFAULT 1,
  pstr_c_p smallint NOT NULL DEFAULT 1,
  pstr_c_m smallint NOT NULL DEFAULT 1,
  patk_p_p smallint NOT NULL DEFAULT 1,
  patk_p_m smallint NOT NULL DEFAULT 1,
  patk_c_p smallint NOT NULL DEFAULT 1,
  patk_c_m smallint NOT NULL DEFAULT 1,
  pdef_p_p smallint NOT NULL DEFAULT 1,
  pdef_p_m smallint NOT NULL DEFAULT 1,
  pdef_c_p smallint NOT NULL DEFAULT 1,
  pdef_c_m smallint NOT NULL DEFAULT 1,
  pres_p_p smallint NOT NULL DEFAULT 1,
  pres_p_m smallint NOT NULL DEFAULT 1,
  pres_c_p smallint NOT NULL DEFAULT 1,
  pres_c_m smallint NOT NULL DEFAULT 1,
  mstr_p_p smallint NOT NULL DEFAULT 1,
  mstr_p_m smallint NOT NULL DEFAULT 1,
  mstr_c_p smallint NOT NULL DEFAULT 1,
  mstr_c_m smallint NOT NULL DEFAULT 1,
  matk_p_p smallint NOT NULL DEFAULT 1,
  matk_p_m smallint NOT NULL DEFAULT 1,
  matk_c_p smallint NOT NULL DEFAULT 1,
  matk_c_m smallint NOT NULL DEFAULT 1,
  mdef_p_p smallint NOT NULL DEFAULT 1,
  mdef_p_m smallint NOT NULL DEFAULT 1,
  mdef_c_p smallint NOT NULL DEFAULT 1,
  mdef_c_m smallint NOT NULL DEFAULT 1,
  mres_p_p smallint NOT NULL DEFAULT 1,
  mres_p_m smallint NOT NULL DEFAULT 1,
  mres_c_p smallint NOT NULL DEFAULT 1,
  mres_c_m smallint NOT NULL DEFAULT 1,
  armor_p smallint NOT NULL DEFAULT 1,
  armor_m smallint NOT NULL DEFAULT 1,
  speed_p smallint NOT NULL DEFAULT 1,
  speed_m smallint NOT NULL DEFAULT 1,
  regen_p smallint NOT NULL DEFAULT 1,
  regen_m smallint NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
);


CREATE TABLE locations (
  location_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  "type" enum('normal','arena','caern','boss') NOT NULL DEFAULT 'normal',
  chance1 smallint NOT NULL DEFAULT 1,
  chance2 smallint NOT NULL DEFAULT 1,
  region_id varchar(32) DEFAULT NULL,
  faction_id varchar(32) DEFAULT NULL,
  faction_value tinyint NOT NULL DEFAULT 1,
  description text,
  picture_url tinytext,
  boss_id varchar(32) DEFAULT NULL,
  boss_status enum('hidden','active','defeated') DEFAULT NULL,
  PRIMARY KEY (location_id),
  KEY region_id (region_id,location_id),
  KEY faction_id (faction_id,location_id),
  KEY "type" ("type",location_id)
);


CREATE TABLE location_events (
  location_id varchar(32) NOT NULL,
  event_id varchar(32) NOT NULL,
  chance smallint NOT NULL DEFAULT 1,
  params varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (location_id,event_id),
  KEY event_id (event_id,location_id)
);


CREATE TABLE location_monsters (
  location_id varchar(32) NOT NULL,
  monster_id varchar(32) NOT NULL,
  chance smallint NOT NULL DEFAULT 1,
  PRIMARY KEY (location_id,monster_id),
  KEY monster_id (monster_id,location_id)
);


CREATE TABLE location_paths (
  location_id varchar(32) NOT NULL,
  destination_id varchar(32) NOT NULL,
  "name" varchar(255) DEFAULT NULL,
  cost_gold int NOT NULL DEFAULT 0,
  cost_mana smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (location_id,destination_id),
  KEY destination_id (destination_id,location_id)
);


CREATE TABLE location_services (
  location_id varchar(32) NOT NULL,
  service_id varchar(32) NOT NULL,
  PRIMARY KEY (location_id,service_id)
);


CREATE TABLE mail (
  message_id int NOT NULL AUTO_INCREMENT,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sender_id int DEFAULT NULL,
  recipient_id int NOT NULL,
  content text NOT NULL,
  PRIMARY KEY (message_id),
  KEY sort (recipient_id,message_id)
);


CREATE TABLE maps (
  map_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  url varchar(255) NOT NULL,
  sort smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (map_id)
);


CREATE TABLE monsters (
  monster_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  class smallint NOT NULL DEFAULT 1,
  "level" smallint NOT NULL DEFAULT 1,
  gold int NOT NULL DEFAULT 0,
  chance1 smallint NOT NULL DEFAULT 1,
  chance2 smallint NOT NULL DEFAULT 1,
  title_id varchar(255) DEFAULT NULL,
  combat_unit_id varchar(32) DEFAULT NULL,
  PRIMARY KEY (monster_id)
);


CREATE TABLE monster_drops (
  monster_id varchar(32) NOT NULL,
  item_id varchar(32) NOT NULL,
  chance smallint NOT NULL DEFAULT 1,
  PRIMARY KEY (monster_id,item_id)
);


CREATE TABLE newsfeed (
  id varchar(255) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  title varchar(255) DEFAULT NULL,
  author varchar(255) DEFAULT NULL,
  content text,
  PRIMARY KEY (id),
  KEY published (published)
);


CREATE TABLE parameters (
  "name" varchar(255) NOT NULL,
  "value" text NOT NULL,
  PRIMARY KEY ("name")
);


CREATE TABLE players (
  player_id int NOT NULL AUTO_INCREMENT,
  "name" varchar(255) DEFAULT NULL,
  login varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL,
  date_created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login datetime DEFAULT NULL,
  roles set('chat','login') NOT NULL DEFAULT 'chat,login',
  skin varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  reset_key varchar(255) DEFAULT NULL,
  reset_until date DEFAULT NULL,
  reset_password varchar(255) DEFAULT NULL,
  PRIMARY KEY (player_id),
  UNIQUE KEY login (login),
  KEY reset_key (reset_key)
);


CREATE TABLE regions (
  region_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  respawn_id varchar(32) DEFAULT NULL,
  picture_url tinytext,
  PRIMARY KEY (region_id)
);


CREATE TABLE rollovers (
  rollover_id smallint NOT NULL AUTO_INCREMENT,
  date_added timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  players_total smallint NOT NULL DEFAULT 0,
  characters_total smallint NOT NULL DEFAULT 0,
  clans_total smallint DEFAULT NULL,
  PRIMARY KEY (rollover_id)
);


CREATE TABLE services (
  service_id varchar(32) NOT NULL,
  "type" enum('bank','healer','npc','shop','temple') NOT NULL,
  "name" varchar(255) NOT NULL,
  faction_id varchar(32) DEFAULT NULL,
  rank_id tinyint DEFAULT NULL,
  description text,
  PRIMARY KEY (service_id)
);


CREATE TABLE service_items (
  service_id varchar(32) NOT NULL,
  item_id varchar(32) NOT NULL,
  "type" enum('normal','drop') NOT NULL DEFAULT 'normal',
  quantity smallint DEFAULT NULL,
  PRIMARY KEY (service_id,item_id),
  KEY item_id (item_id,service_id)
);


CREATE TABLE spells (
  spell_id varchar(32) NOT NULL,
  "name" varchar(255) NOT NULL,
  max_level tinyint NOT NULL DEFAULT 7,
  max_cost smallint NOT NULL DEFAULT 10,
  min_cost smallint NOT NULL DEFAULT 1,
  handle varchar(255) DEFAULT NULL,
  PRIMARY KEY (spell_id)
);

INSERT INTO spells (spell_id, `name`, max_level, max_cost, min_cost, handle) VALUES
('identify', 'Identyfikacja', 6, 45, 25, 'Identify'),
('scout', 'Badanie Terenu', 4, 35, 20, 'Scout'),
('vchar', 'Poznanie Postaci', 7, 60, 30, 'ScanCharacter'),
('vitem', 'Poznanie Przedmiotu', 6, 40, 20, 'ScanItem'),
('vmonster', 'Poznanie Potwora', 5, 40, 20, 'ScanMonster');

CREATE TABLE titles (
  title_id varchar(32) NOT NULL,
  name_f varchar(255) NOT NULL DEFAULT '',
  name_m varchar(255) NOT NULL DEFAULT '',
  name_n varchar(255) NOT NULL DEFAULT '',
  "type" enum('normal','special') NOT NULL DEFAULT 'normal',
  PRIMARY KEY (title_id),
  KEY "type" ("type",title_id)
);
