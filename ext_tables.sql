CREATE TABLE tx_openoap_domain_model_call (
	title varchar(255) NOT NULL DEFAULT '',
	intro_text text NOT NULL DEFAULT '',
	teaser_text varchar(255) NOT NULL DEFAULT '',
	shortcut varchar(255) NOT NULL DEFAULT '',
	emails varchar(255) NOT NULL DEFAULT '',
	call_start_time datetime DEFAULT NULL,
	call_end_time datetime DEFAULT NULL,
	fe_user_exceptions varchar(255) NOT NULL DEFAULT '',
	proposal_pid int(11) NOT NULL DEFAULT '0',
	form_pages int(11) unsigned NOT NULL DEFAULT '0',
	usergroup text,
	items int(11) unsigned NOT NULL DEFAULT '0',
	word_template int(11) unsigned DEFAULT '0',
    word_header_logo int(11) unsigned DEFAULT '0',
	logo int(11) unsigned DEFAULT '0',
	blocked_languages varchar(255) NOT NULL DEFAULT '',
    word_styles text,
    anonym tinyint(3) DEFAULT '0' NOT NULL,
    survey_codes text,
);

CREATE TABLE tx_openoap_domain_model_formpage (
	title varchar(255) NOT NULL DEFAULT '',
	menu_title varchar(255) NOT NULL DEFAULT '',
	internal_title varchar(255) NOT NULL DEFAULT '',
	intro_text text,
	type int(11) DEFAULT '0' NOT NULL,
	item_groups int(11) unsigned NOT NULL DEFAULT '0',
	modificators int(11) unsigned NOT NULL DEFAULT '0'
);

CREATE TABLE tx_openoap_domain_model_formgroup (
	title varchar(255) NOT NULL DEFAULT '',
    internal_title varchar(255) NOT NULL DEFAULT '',
	intro_text text,
	help_text text NOT NULL DEFAULT '',
	model_name varchar(255) NOT NULL DEFAULT '',
	repeatable_min int(11) NOT NULL DEFAULT 1,
	repeatable_max int(11) NOT NULL DEFAULT 1,
	display_type int(11) NOT NULL DEFAULT 0,
	items int(11) unsigned NOT NULL DEFAULT '0',
	group_title int(11) unsigned NOT NULL DEFAULT '0',
	modificators int(11) unsigned NOT NULL DEFAULT '0',
	type int(11) DEFAULT '0' NOT NULL,
	item_groups int(11) unsigned NOT NULL DEFAULT '0'
);

CREATE TABLE tx_openoap_domain_model_formitem (
	question varchar(255) NOT NULL DEFAULT '',
    internal_title varchar(255) NOT NULL DEFAULT '',
	intro_text text,
	help_text text NOT NULL DEFAULT '',
	type int(11) DEFAULT '0' NOT NULL,
	enabled_filter smallint(1) unsigned NOT NULL DEFAULT '0',
	filter_label varchar(255) NOT NULL DEFAULT '',
	enabled_info smallint(1) unsigned NOT NULL DEFAULT '0',
	enabled_title smallint(1) unsigned NOT NULL DEFAULT '0',
	additional_value smallint(1) unsigned NOT NULL DEFAULT '0',
	default_value text NOT NULL DEFAULT '',
	unit varchar(255) NOT NULL DEFAULT '',
	additional_label varchar(255) NOT NULL DEFAULT '',
	options int(11) unsigned NOT NULL DEFAULT '0',
	validators int(11) unsigned NOT NULL DEFAULT '0',
	modificators int(11) unsigned NOT NULL DEFAULT '0'
);

CREATE TABLE tx_openoap_domain_model_itemoption (
	title varchar(255) NOT NULL DEFAULT '',
	option_group varchar(255) NOT NULL DEFAULT '',
	options text NOT NULL DEFAULT '',
	type int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_openoap_domain_model_itemvalidator (
	title varchar(255) NOT NULL DEFAULT '',
	type int(11) DEFAULT '0' NOT NULL,
	param1 varchar(255) NOT NULL DEFAULT '',
	param2 varchar(255) NOT NULL DEFAULT '',
	item int(11) unsigned NOT NULL DEFAULT '0'
);

CREATE TABLE fe_users (
	tx_openoap_company_email varchar(255) NOT NULL DEFAULT '',
	tx_openoap_preferred_lang varchar(255) NOT NULL DEFAULT '',
	tx_openoap_privacypolicy tinyint(3) NOT NULL DEFAULT '0',
	tx_openoap_proposals int(11) unsigned NOT NULL DEFAULT '0',
	tx_openoap_salutation int(11) unsigned NOT NULL DEFAULT '0',
	tx_extbase_type varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_openoap_domain_model_proposal (
	title text NOT NULL DEFAULT '',
	signature int(11) DEFAULT '0' NOT NULL,
	state int(11) DEFAULT '0' NOT NULL,
	archived smallint(1) unsigned NOT NULL DEFAULT '0',
	meta_information text NOT NULL DEFAULT '',
	tx_openoap_call int(11) unsigned DEFAULT '0',
	answers text NOT NULL,
	comments text NOT NULL,
	applicant int(11) unsigned DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '-1' NOT NULL,
	fe_language_uid int(11) DEFAULT '0' NOT NULL,
	edit_tstamp int(11) DEFAULT '0' NOT NULL,
	submit_tstamp int(11) DEFAULT '0' NOT NULL,
	rejection_tstamp int(11) DEFAULT '0' NOT NULL,
	rejection_email varchar(255) NOT NULL DEFAULT ''
);

CREATE TABLE tx_openoap_domain_model_answer (
	value text NOT NULL DEFAULT '',
	type int(11) DEFAULT '0' NOT NULL,
	element_counter int(11) DEFAULT '0' NOT NULL,
	group_counter_0 int(11) DEFAULT '0' NOT NULL,
	group_counter_1 int(11) DEFAULT '0' NOT NULL,
	additional_value text NOT NULL DEFAULT '',
	past_answers text NOT NULL DEFAULT '',
	item int(11) unsigned DEFAULT '0',
	model int(11) unsigned DEFAULT '0',
	comments text NOT NULL,
	sys_language_uid int(11) DEFAULT '-1' NOT NULL
);

CREATE TABLE tx_openoap_domain_model_grouptitle (
	title varchar(255) NOT NULL DEFAULT '',
	internal_title varchar(255) NOT NULL DEFAULT ''
);

CREATE TABLE tx_openoap_domain_model_comment (
	text text NOT NULL DEFAULT '',
	source int(11) NOT NULL DEFAULT '0',
	code int(11) NOT NULL DEFAULT '0',
	state int(11) NOT NULL DEFAULT '0',
	proposal int(11) unsigned NOT NULL DEFAULT '0',
	answer int(11) unsigned DEFAULT '0',
    cruser_id int(11) unsigned NOT NULL DEFAULT '0',
	sys_language_uid int(11) DEFAULT '-1' NOT NULL
);

CREATE TABLE tx_openoap_domain_model_formmodificator (
	title varchar(255) NOT NULL DEFAULT '',
	item int(11) DEFAULT '0' NOT NULL,
	logic int(11) DEFAULT '0' NOT NULL,
	value varchar(255) NOT NULL DEFAULT '',
	items int(11) unsigned NOT NULL DEFAULT '0'
);

CREATE TABLE tx_openoap_domain_model_call (
	categories int(11) unsigned DEFAULT '0' NOT NULL,
	call_group int(11) unsigned DEFAULT '0' NOT NULL,
    supporter int(11) unsigned DEFAULT '0' NOT NULL,
    type int(11) unsigned DEFAULT '0' NOT NULL,
    extern_link varchar(255) NOT NULL DEFAULT '',
    hint text NOT NULL DEFAULT '',
);

CREATE TABLE tx_openoap_domain_model_formpage (
	categories int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE fe_users (
	categories int(11) unsigned DEFAULT '0' NOT NULL,
    street_num varchar(255) NOT NULL DEFAULT '',
    company_email varchar(255) NOT NULL DEFAULT '',
    preferred_lang varchar(255) NOT NULL DEFAULT '',
    privacypolicy tinyint(3) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_openoap_domain_model_callgroup (
    title varchar(255) NOT NULL DEFAULT '',
    description varchar(255) NOT NULL DEFAULT '',
    default_giz varchar(255) NOT NULL DEFAULT '',
    default_deg varchar(255) NOT NULL DEFAULT '',
    country_deg varchar(255) NOT NULL DEFAULT '',
    country_giz varchar(255) NOT NULL DEFAULT '',
    blocked_languages varchar(255) NOT NULL DEFAULT '',
    sorting int(11) NOT NULL DEFAULT '0',
);

#
# Table structure for table 'tx_openoap_domain_model_supporter'
#
CREATE TABLE tx_openoap_domain_model_supporter (
    title varchar(255) NOT NULL DEFAULT '' ,
    event_proposal_submitted_mailtext text NOT NULL DEFAULT '' ,
    event_proposal_in_revision_mailtext text NOT NULL DEFAULT '' ,
    event_proposal_accepted_mailtext text NOT NULL DEFAULT '' ,
    event_proposal_declined_mailtext text NOT NULL DEFAULT '' ,
);
