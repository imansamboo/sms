<?php
$additionalDomainFields['.id.au'][]=[
	"Name"       =>  "Eligibility Type",
	"LangVar"    =>  "eligibilityType",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'Company'=>'Company',
		'RegisteredBusiness'=>'Registered Business',
		'SoleTrader'=>'Sole Trader',
		'Partnership'=>'Partnership',
		'TrademarkOwner'=>'Trademark Owner',
		'PendingTMOwner'=>'Pending TM Owner',
		'CitizenResident'=>'Citizen Resident',
		'IncorporatedAssociation'=>'IncorporatedAssociation',
		'Club'=>'Club',
		'NonProfitOrganisation'=>'NonProfit Organisation',
		'Charity'=>'Charity',
		'TradeUnion'=>'Trade Union',
		'IndustryBody'=>'Industry Body',
		'CommercialStatutoryBody'=>'Commercial Statutory Body',
		'PoliticalParty'=>'Political Party',
		'Other'=>'Other',
		'Non-profit'=>'Non-profit',
		'Organisation'=>'Organisation',
		'Citizen/Resident'=>'Citizen/Resident',
	],
	"Default"    =>  "Company",
	"Description"=> 'Specify what makes the registrant eligible to register the .com.au, .net.au, .org.au or .id.au domain name.'
];
$additionalDomainFields['.id.au'][]=[
	"Name"      =>  "Eligibility Type Relationship",
	"LangVar"   =>  "eligibilityTypeRelationship",
	"Type"      =>  "dropdown",
	"Options"   =>  [
		'1'=>'(2LD Domain name is an exact match, an acronym or abbreviation of the company or trading name, organisation or association name, or trademark)',
		'2'=>'(2LD Domain Name is closely and substantially connected to the organisation or activities undertaken by the organisation.)',
	],
	"Default"   =>  "1",
	"Description"=> 'This indicates the relationship between the Eligibility Type (e.g. business name#) and domain name. Specify the Policy reference number as appropriate from the values below.'
];

$additionalDomainFields['.com.au']=$additionalDomainFields['.org.au']=$additionalDomainFields['.net.au']=$additionalDomainFields['.id.au'];

$additionalDomainFields['.ca'][]=[
	"Name"       =>  "Legal Type",
	"LangVar"    =>  "legalType",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'ABO'=>'Aboriginal Peoples indigenous to Canada',
		'ASS'=>'Canadian Unincorporated Association',
		'CCO'=>'Corporation (Canada or Canadian province or territory)',
		'CCT'=>'Canadian citizen,EDU|Canadian Educational Institution',
		'GOV'=>'Government or government entity in Canada',
		'HOP'=>'Canadian Hospital',
		'INB'=>'Indian Band recognized by the Indian Act of Canada',
		'LAM'=>'Canadian Library. Archive or Museum',
		'LGR'=>'Legal Rep. of a Canadian Citizen or Permanent Resident',
		'MAJ'=>'Her Majesty the Queen',
		'OMK'=>'Official mark registered in Canada',
		'PLT'=>'Canadian Political Party',
		'PRT'=>'Partnership Registered in Canada',
		'RES'=>'Permanent Resident of Canada',
		'TDM'=>'Trade-mark registered in Canada (by a non-Canadian owner)',
		'TRD'=>'Canadian Trade Union',
		'TRS'=>'Trust established in Canada'
	],
	"Default"    =>  "ABO",
	"Description"=> ''
];

$additionalDomainFields['.com'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'AFR'=>'(Afrikaans)',
		'ALB'=>'(Albanian)',
		'ARA'=>'(Arabic)',
		'ARG'=>'(Aragonese)',
		'ARM'=>'(Armenian)',
		'ASM'=>'(Assamese)',
		'AST'=>'(Asturian)',
		'AVE'=>'(Avestan)',
		'AWA'=>'(Awadhi)',
		'AZE'=>'(Azerbaijani)',
		'BAN'=>'(Balinese)',
		'BAL'=>'(Baluchi)',
		'BAS'=>'(Basa)',
		'BAK'=>'(Bashkir)',
		'BAQ'=>'(Basque)',
		'BEL'=>'(Belarusian)',
		'BEN'=>'(Bengali)',
		'BHO'=>'(Bhojpuri)',
		'BOS'=>'(Bosnian)',
		'BUL'=>'(Bulgarian)',
		'BUR'=>'(Burmese)',
		'CAR'=>'(Carib)',
		'CAT'=>'(Catalan)',
		'CHE'=>'(Chechen)',
		'CHI'=>'(Chinese)',
		'CHV'=>'(Chuvash)',
		'COP'=>'(Coptic)',
		'COS'=>'(Corsican)',
		'SCR'=>'(Croatian)',
		'CZE'=>'(Czech)',
		'DAN'=>'(Danish)',
		'DIV'=>'(Divehi)',
		'DOI'=>'(Dogri)',
		'DUT'=>'(Dutch)',
		'ENG'=>'(English)',
		'EST'=>'(Estonian)',
		'FAO'=>'(Faroese)',
		'FIJ'=>'(Fijian)',
		'FIN'=>'(Finnish)',
		'FRE'=>'(French)',
		'FRI'=>'(Frisian)',
		'GLA'=>'(Gaelic)',
		'GEO'=>'(Georgian)',
		'GER'=>'(German)',
		'GON'=>'(Gondi)',
		'GRE'=>'(Greek)',
		'GUJ'=>'(Gujarati)',
		'HEB'=>'(Hebrew)',
		'HIN'=>'(Hindi)',
		'HUN'=>'(Hungarian)',
		'ICE'=>'(Icelandic)',
		'INC'=>'(Indic)',
		'IND'=>'(Indonesian)',
		'INH'=>'(Ingush)',
		'GLE'=>'(Irish)',
		'ITA'=>'(Italian)',
		'JPN'=>'(Japanese)',
		'JAV'=>'(Javanese)',
		'KAS'=>'(Kashmiri)',
		'KAZ'=>'(Kazakh)',
		'KHM'=>'(Khmer)',
		'KIR'=>'(Kirghiz)',
		'KOR'=>'(Korean)',
		'KUR'=>'(Kurdish)',
		'LAO'=>'(Lao)',
		'LAV'=>'(Latvian)',
		'LIT'=>'(Lithuanian)',
		'LTZ'=>'(Luxembourgisch)',
		'MAC'=>'(Macedonian)',
		'MAL'=>'(Malayalam)',
		'MAY'=>'(Malay)',
		'MLT'=>'(Maltese)',
		'MAO'=>'(Maori)',
		'MOL'=>'(Moldavian)',
		'MON'=>'(Mongolian)',
		'NEP'=>'(Nepali)',
		'NOR'=>'(Norwegian)',
		'ORI'=>'(Oriya)',
		'OSS'=>'(Ossetian)',
		'PAN'=>'(Panjabi)',
		'PER'=>'(Persian)',
		'POL'=>'(Polish)',
		'POR'=>'(Portuguese)',
		'PUS'=>'(Pushto)',
		'RAJ'=>'(Rajasthani)',
		'RUM'=>'(Romanian)',
		'RUS'=>'(Russian)',
		'SMO'=>'(Samoan)',
		'SAN'=>'(Sanskrit)',
		'SRD'=>'(Sardinian)',
		'SCC'=>'(Serbian)',
		'SND'=>'(Sindhi)',
		'SIN'=>'(Sinhalese)',
		'SLO'=>'(Slovak)',
		'SLV'=>'(Slovenian)',
		'SOM'=>'(Somali)',
		'SPA'=>'(Spanish)',
		'SWA'=>'(Swahili)',
		'SWE'=>'(Swedish)',
		'SYR'=>'(Syriac)',
		'TGK'=>'(Tajik)',
		'TAM'=>'(Tamil)',
		'TEL'=>'(Telugu)',
		'THA'=>'(Thai)',
		'TIB'=>'(Tibetan)',
		'TUR'=>'(Turkish)',
		'UKR'=>'(Ukrainian)',
		'URD'=>'(Urdu)',
		'UZB'=>'(Uzbek)',
		'VIE'=>'(Vietnamese)',
		'WEL'=>'(Welsh)',
		'YID'=>'(Yiddish)',
	],
	"Default"    =>  "PER",
	"Description"=> 'Optional: Only necessary for IDN domains'
];

$additionalDomainFields['.net']=$additionalDomainFields['.com'];

$additionalDomainFields['.org'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'ZH-TW'=>'(Chinese (Traditional))',
		'ZH-CN'=>'(Chinese (Simplified))',
		'DA'=>'(Danish)',
		'DE'=>'(German)',
		'HU'=>'(Hungarian)',
		'IS'=>'(Icelandic)',
		'KO'=>'(Korean (Hangul))',
		'LV'=>'(Latvian)',
		'LT'=>'(Lithuanian)',
		'PL'=>'(Polish)',
		'ES'=>'(Spanish)',
		'SV'=>'(Swedish)',
		'BS'=>'(Bosnian)',
		'BG'=>'(Bulgarian)',
		'BE'=>'(Belarusian)',
		'MK'=>'(Macedonian)',
		'RU'=>'(Russian)',
		'SR'=>'(Serbian)',
		'UK'=>'(Ukrainian)',
	],
	"Default"    =>  "DE",
	"Description"=> 'Optional: Only necessary for IDN domains'
];
$additionalDomainFields['.xxx'][]=[
	"Name"       =>  "Defensive",
	"LangVar"    =>  "defensive",
	"Type"       =>  "text",
	"Size"       => "20",
	"Default"    => "",
	"Required"   => true,
	"Description"=> 'This field is optional, should be set to 1 to block domain (defensive registration by non-member of the "Sponsored Community")'
];

$additionalDomainFields['.voto'][]=[
	"Name"       =>  "voto Acceptance",
	"LangVar"    =>  "votoAcceptance",
	"Type"       =>  "text",
	"Size"       => "20",
	"Default"    => "",
	"Required"   => true,
	"Description"=> 'This field is required, should be set to 1 to confirm acceptance of .voto registry policies'
];
$additionalDomainFields['.vote'][]=[
	"Name"       =>  "vote Acceptance",
	"LangVar"    =>  "voteAcceptance",
	"Type"       =>  "text",
	"Size"       => "20",
	"Default"    => "",
	"Required"   => true,
	"Description"=> 'This field is required, should be set to 1 to confirm acceptance of .vote registry policies'
];

$additionalDomainFields['.scot'][]=[
	"Name"       =>  "intended Use",
	"LangVar"    =>  "intendedUse",
	"Type"       =>  "text",
	"Size"       => "500",
	"Default"    => "",
	"Required"   => true,
	"Description"=> 'This field is required, should contain information on how the domain name will be used'
];
$additionalDomainFields['.scot'][]=[
	"Name"       =>  "domain Name Variants",
	"LangVar"    =>  "domainNameVariants",
	"Type"       =>  "text",
	"Size"       => "500",
	"Default"    => "",
	"Required"   => true,
	"Description"=> 'This field is optional, should be an array of strings, representing appropriate domain variant names'
];
$additionalDomainFields['.scot'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'Latn'=> '(Latin)'
	],
	"Default"    =>  "Latn",
	"Description"=> 'Optional: Only necessary for IDN domains'
];
$additionalDomainFields['eus']=$additionalDomainFields['.gal']=$additionalDomainFields['.cat']=$additionalDomainFields['.barcelona']=$additionalDomainFields['.scot'];

$additionalDomainFields['.pl'][]=[
	"Name"       =>  "intended Use",
	"LangVar"    =>  "intendedUse",
	"Type"       =>  "text",
	"Size"       => "500",
	"Default"    => "",
	"Required"   => true,
	"Description"=> 'This field is required, should contain information on how the domain name will be used'
];

$additionalDomainFields['.شبكة'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'ar'=>'(Arabic)'
	],
	"Default"    =>  "ar",
	"Description"=> 'Mandatory for IDN domains'
];
$additionalDomainFields['.موقع']=$additionalDomainFields['.شبكة'];

$additionalDomainFields['.cайт'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'Cyrl'=>'(Cyrillic)'
	],
	"Default"    =>  "Cyrl",
	"Description"=> 'Mandatory for IDN domains'
];
$additionalDomainFields['.онлайн']=$additionalDomainFields['.cайт'];

$additionalDomainFields['.democrat']=
$additionalDomainFields['.dance']=
$additionalDomainFields['.ninja']=
$additionalDomainFields['.social']=
$additionalDomainFields['.futbol']=
$additionalDomainFields['.reviews']=
$additionalDomainFields['.pub']=
$additionalDomainFields['.moda']=
$additionalDomainFields['.consulting']=
$additionalDomainFields['.rocks']=
$additionalDomainFields['.actor']=
$additionalDomainFields['.republican']=
$additionalDomainFields['.attorney']=
$additionalDomainFields['.lawyer']=
$additionalDomainFields['.airforce']=
$additionalDomainFields['.vet']=
$additionalDomainFields['.army']=
$additionalDomainFields['.navy']=
$additionalDomainFields['.mortgage']=
$additionalDomainFields['.market']=
$additionalDomainFields['.engineer']=
$additionalDomainFields['.software']=
$additionalDomainFields['.auction']=
$additionalDomainFields['.dentist']=
$additionalDomainFields['.rehab']=
$additionalDomainFields['.gives']=
$additionalDomainFields['.degree']=
$additionalDomainFields['.forsale']=
$additionalDomainFields['.rip']=
$additionalDomainFields['.band'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'fr'=>'fr',
		'es'=>'es'
	],
	"Default"    =>  "fr",
	"Description"=> 'Optional: Only necessary for IDN domains'
];
$additionalDomainFields['.immobilien']=
$additionalDomainFields['.kaufen']=
$additionalDomainFields['.haus'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'fr'=>'(French)',
		'es'=>'(Spanish)',
		'de'=>'(German)',
	],
	"Default"    =>  "de",
	"Description"=> 'Optional: Only necessary for IDN domains'
];
$additionalDomainFields['.nrw'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'de'=>'(German)'
	],
	"Default"    =>  "de",
	"Description"=> 'Optional: Only necessary for IDN domains'
];

$additionalDomainFields['.орг'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'ru'=>'(Russian)'
	],
	"Default"    =>  "ru",
	"Description"=> 'Mandatory for IDN domains'
];
$additionalDomainFields['.机构'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'ZH-TW'=>'(Chinese (Traditional))',
		'ZH-CN'=>'(Chinese (Simplified))',
	],
	"Default"    =>  "fr",
	"Description"=> 'Mandatory for IDN domains'
];
$additionalDomainFields['.संगठन'][]=[
	"Name"       =>  "idn Script",
	"LangVar"    =>  "idnScript",
	"Type"       =>  "dropdown",
	"Options"    =>  [
		'hin-deva'=>'hin-deva'
	],
	"Default"    =>  "hin-deva",
	"Description"=> 'Mandatory for IDN domains'
];

