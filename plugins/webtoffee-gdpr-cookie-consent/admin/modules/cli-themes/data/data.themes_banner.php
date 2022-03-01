<?php
//button_1 : Accept [cookie_button]
//button_2 : Read More [cookie_link]
//button_3 : Reject [cookie_reject]
//button_4 : Settings [cookie_settings]
//button_5 : Close [cookie_close]

//note : always add empty border when adding styles Eg: solid 0px #ccc;

//always follow the naming structure for other bar types Eg: cli_theme_{bar type}_default
//pls add width param for preview in default themes eg: width:100%; for banner type.
$cli_theme_banner_default=array
(
	'title'=>'Theme_banner current',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'',
			"style"=>"padding:10px; box-sizing:border-box; float:left; width:100%; border:solid 0px #fff; font-size:14px;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:center; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:16px; font-weight:bold; text-transform:none;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; padding:8px 16px 8px; border-radius:4px; text-align:center; font-size: 12px; border:solid 0px #fff; line-height:18px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display:inline-block; text-align:center; font-size:14px; padding:8px 16px 8px; border:solid 0px #fff; line-height:18px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; padding:8px 16px 8px; border-radius:4px; text-align:center; font-size: 12px; border:solid 0px #fff; line-height:18px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display:inline-block; padding:8px 16px 8px; border-radius:4px; text-align:center; font-size: 12px; border:solid 0px #fff; line-height:18px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #000; color: #000; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>1,
			"text"=>"Accept All",
			"style"=>"display:inline-block; padding:8px 16px 8px; border-radius:4px; text-align:center; font-size: 12px; border:solid 0px #fff; line-height:18px;"
		),
	)
);


$cli_theme_banner1=array
(
	'title'=>'Theme_banner1',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'<div class="cli-bar-container cli-style-v2"><div class="cli-bar-message">We use cookies on our website to give you the most relevant experience by remembering your preferences and repeat visits. By clicking “Accept”, you consent to the use of ALL the cookies. However you may visit Cookie Settings to provide a controlled consent.</div><div class="cli-bar-btn_container">[cookie_settings][cookie_button]</div></div>',
			"style"=>"color:#444444;background-color:#ffffff;padding: 13px 20px;text-align:left;"
		),
		"heading"=>array(
			"status"=>0,
			"text"=>"This website uses cookies",
			"style"=>"font-size: 16px;margin: 10px 0;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"margin: 5px 5px 5px 30px; border-radius: 0px; padding: 8px 25px; color:#ffffff; background-color:#61a229;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read more",
			"style"=>"display: inline-block; background:none; border-radius:0px; color: #fff; text-align:left; font-size:14px; padding:5px 8px; padding-left:5px; margin-left:0px; line-height:16px; border:solid 0px #fff; text-decoration:underline; font-weight:600;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"margin: 5px 5px 5px 10px; border-radius: 0px; padding: 8px 25px; color:#ffffff; background-color:#61a229;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Cookie settings",
			"style"=>"border-bottom: 1px solid; color: rgb(137, 136, 136);text-decoration: none;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #fff; color: #fff; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"margin: 5px 5px 5px 30px; border-radius: 0px; padding: 8px 25px; color:#ffffff; background-color:#61a229;"
		),
	)
);
$cli_theme_banner2=array
(
	'title'=>'Theme_banner2',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'<div class="wt-cli-template cli-style-v3">We use cookies in our website to give you the most relevant experience by remembering your preferences and repeat visits. By clicking “Accept”, you consent to the use of the cookies explicitly. Visit Cookie Settings to know more about the cookies used on our website.<div class="cli-bar-actions">[wt_cli_category_widget]<div class="cli-bar-btn_container">[cookie_settings][cookie_button][cookie_accept_all]</div></div></div>',
			"style"=>"color:#444444;background-color:#ffffff;text-align:left;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"font-size: 16px;margin: 10px 0;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"margin: 5px 5px 5px 30px; border-radius: 0px; padding: 8px 25px; color:#ffffff; background-color:#61a229;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read more",
			"style"=>"display: inline-block; background:none; border-radius:0px; color: #fff; text-align:left; font-size:14px; padding:5px 8px; padding-left:5px; margin-left:0px; line-height:16px; border:solid 0px #fff; text-decoration:underline; font-weight:600;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"margin: 5px 5px 5px 10px; border-radius: 0px; padding: 8px 25px; color:#ffffff; background-color:#61a229;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Cookie settings",
			"style"=>"border-bottom: 1px solid; color: rgb(137, 136, 136);text-decoration: none;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #fff; color: #fff; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>1,
			"text"=>"Accept All",
			"style"=>"margin: 5px 5px 5px 5px; border-radius: 0px; padding: 8px 25px; color:#ffffff; background-color:#61a229;"
		),
	)
);
$cli_theme_banner3=array
(
	'title'=>'Theme_banner3',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. We\'ll assume you\'re ok with this, but you can opt-out if you wish [cookie_link][cookie_settings][cookie_reject][cookie_button][cookie_close]',
			"style"=>"background: #fff; border:solid 0px #dbdbdb; color: #000; padding:22px; box-sizing: border-box; float:left; width:100%; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#000; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; background:#04b47c; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display: inline-block; background:none; border-radius:0px; color:#000; text-align:center; font-size:14px; text-decoration:underline; padding:5px 8px; margin-left:0px; border:solid 0px #fff; line-height:18px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; background:#f4f4f9; border-radius:2px; color:#646c9b; text-align: center; font-size:14px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:#fff; border-radius:2px; color:#646c9b; text-align: center; font-size:14px; padding:5px 8px; margin-left:10px; border:solid 1px #f4f4f9; line-height:18px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #dddddd; color: #dddddd; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; background:#04b47c; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
	)
);


$cli_theme_banner4=array
(
	'title'=>'Theme_banner4',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. We\'ll assume you\'re ok with this, but you can opt-out if you wish [cookie_link][cookie_reject][cookie_button][cookie_settings][cookie_close]',
			"style"=>"background: #fff; border:solid 1px #dbdbdb; color:#000; padding:22px; box-sizing: border-box;  float:left; width:100%; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#000; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:#04b47c; border-radius:0px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display: inline-block; background:none; border-radius:2px; color: #000; text-align: center; font-size: 12px; padding:5px 8px; margin-left:0px; font-weight: bold; text-decoration: underline; border:solid 0px #fff; line-height:18px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#fff; border-radius:2px; color:#646c9b; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 1px #f4f4f9; line-height:18px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:#000; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #dddddd; color: #dddddd; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:#04b47c; border-radius:0px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
	)
);

$cli_theme_banner5=array
(
	'title'=>'Theme_banner5',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. We\'ll assume you\'re ok with this, but you can opt-out if you wish [cookie_button] [cookie_link]',
			"style"=>"background: #fff; border:solid 1px #dbdbdb; color: #000; padding:22px; box-sizing: border-box;  float: left; width:100%; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#000; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:#000; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display: inline-block; background:none; border-radius:2px; color: #000; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; font-weight: bold; text-decoration: none; border:solid 0px #fff; line-height:18px;"
		),
		"button_3"=>array(
			"status"=>0,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#ccc; border-radius:0px; color: #333; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_4"=>array(
			"status"=>0,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:#ccc; border-radius:0px; color: #333; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_5"=>array(
			"status"=>0,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #000; color: #000; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:#000; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
	)
);
$cli_theme_banner6=array
(
	'title'=>'Theme_banner6',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. We\'ll assume you\'re ok with this, but you can opt-out if you wish [cookie_button] [cookie_link]',
			"style"=>"background: #fff; border:solid 1px #dbdbdb; color: #000; padding:22px; box-sizing: border-box;  float: left; width:100%; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#000; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:#888787; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px;  margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display: inline-block; background:none; border-radius:2px; color:#0d56c4; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; font-weight: bold; text-decoration: none; border:solid 0px #fff; line-height:18px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#ccc; border-radius:2px; color: #333; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:#ccc; border-radius:2px; color: #333; text-align: center; font-size: 12px; padding:5px 8px;  margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
		"button_5"=>array(
			"status"=>0,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #000; color: #000; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:#888787; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px;  margin-left:10px; border:solid 0px #fff; line-height:18px;"
		),
	)
);
$cli_theme_banner7=array
(
	'title'=>'Theme_banner7',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. We\'ll assume you\'re ok with this, but you can opt-out if you wish [cookie_link][cookie_reject][cookie_button][cookie_settings][cookie_close]',
			"style"=>"background: #000; color: #fff; padding:22px; box-sizing: border-box; float: left; width:100%; border:solid 0px #fff; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#fff; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:#f4354c; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; float: right; border:solid 0px #fff; line-height:18px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display: inline-block; background:none; border-radius:2px; color:#fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:0px; font-weight:normal; text-decoration:underline; border:solid 0px #fff; line-height:18px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#2c2b2b; border-radius:2px; color:#fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; float: right; border:solid 0px #fff; line-height:18px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:#000; border-radius:2px; color:#fff; text-align: center; font-size: 12px; padding:3px 6px; margin-left:10px; font-weight:normal; text-decoration:none; border:solid 1px #2c2b2b; line-height:18px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #000; color: #000; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:#f4354c; border-radius:2px; color: #fff; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; float: right; border:solid 0px #fff; line-height:18px;"
		),
	)
);
$cli_theme_banner8=array
(
	'title'=>'Theme_banner8',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. [cookie_link][cookie_settings][cookie_reject][cookie_button][cookie_close]',
			"style"=>"background: #000; color: #fff; font-weight:600; padding:22px; box-sizing:border-box; float: left; width:100%; border:solid 0px #fff; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#fff; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:none; border-radius:0px; border:solid 1px #fff; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:3px 6px;  margin-left:10px; line-height:18px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display: inline-block; background:none; border-radius:0px; border:solid 0px #fff; color: #fff; text-align:left; font-weight:600; font-size: 12px; padding:5px 8px; padding-left:5px;  margin-left:0px; text-decoration:underline; line-height:18px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#2c2b2b; border-radius:0px; border:solid 0px #333; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:5px 8px; margin-left:10px; line-height:18px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:none; border-radius:0px; border:solid 1px #343434; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:3px 6px; margin-left:10px; text-decoration:none; line-height:18px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #dddddd; color: #dddddd; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:none; border-radius:0px; border:solid 1px #fff; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:3px 6px;  margin-left:10px; line-height:18px;"
		),
	)
);
$cli_theme_banner9=array
(
	'title'=>'Theme_banner9',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. [cookie_link][cookie_reject][cookie_button][cookie_settings][cookie_close]',
			"style"=>"background: #000; color: #fff; font-weight:normal; padding:20px 40px; box-sizing: border-box;  border-radius:52px; float:left; line-height:30px; width:100%; border:solid 0px #fff; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#fff; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; width:90%; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:#d9063c; border-radius:52px; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:9px 12px; margin-left:10px; border:solid 0px #fff; line-height:16px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read more",
			"style"=>"display: inline-block; background:none; color: #fff; text-align:left; font-weight:600; font-size: 12px; padding:9px 12px; margin-left:0px; border:solid 0px #fff; line-height:16px; text-decoration:underline; padding-left:5px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#2c2b2b; border-radius:52px; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:9px 12px; margin-left:10px; border:solid 0px #fff; line-height:16px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:none; border-radius:52px; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:7px 10px; margin-left:10px; border:solid 1px #2c2b2b; line-height:16px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 0px #fff; color: #808080; text-align: center; font-size:16px; width:22px; height: 22px; line-height: 22px; margin-right:0px; margin-top:-5px; float: right; font-weight:600; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:#d9063c; border-radius:52px; color: #fff; text-align: center; font-weight: normal; font-size: 12px; padding:9px 12px; margin-left:10px; border:solid 0px #fff; line-height:16px;"
		),
	)
);
$cli_theme_banner10=array
(
	'title'=>'Theme_banner10',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. [cookie_link][cookie_reject][cookie_button][cookie_settings][cookie_close]',
			"style"=>"background: #005eb8; color: #fff; font-weight:normal; padding:22px; box-sizing:border-box; float:left; width:100%; border:solid 0px #fff; font-size:14px; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#fff; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:#fff; border-radius:13px; color: #005eb8; text-align: center; font-weight: normal; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:16px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read more",
			"style"=>"display: inline-block; background:none; color:#fff; text-align: center; font-weight:600; font-size:14px; padding:5px 8px; padding-left:5px; text-decoration:underline; margin-left:0px; border:solid 0px #fff;line-height:18px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#1a69b5; border-radius:13px; color:#5093d3; text-align: center; font-weight: normal; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:16px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display:inline-block; background:#005eb8; border-radius:13px; color:#ffffff; text-align: center; font-weight: normal; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:16px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 0px #fff; color: #1d6bb7; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:#fff; border-radius:13px; color: #005eb8; text-align: center; font-weight: normal; font-size: 12px; padding:5px 8px; margin-left:10px; border:solid 0px #fff; line-height:16px;"
		),
	)
);
$cli_theme_banner11=array
(
	'title'=>'Theme_banner11',
	'config'=>array(
		"bar"=>array(
			"type"=>'banner',
			"text"=>'This website uses cookies to improve your experience. We\'ll assume you\'re ok with this, but you can opt-out if you wish[cookie_link][cookie_settings][cookie_reject][cookie_button][cookie_close]',
			"style"=>"background: #000; color:#fff; padding:22px; box-sizing: border-box; font-weight: 600; line-height:30px; float:left; width:100%; border:solid 0px #fff; text-align:left; font-size:14px; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; color:#fff; line-height:24px; font-size:16px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display: inline-block; background:#fff; border-radius:14px; color: #4b4949; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; line-height:16px; border:solid 0px #fff;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read more",
			"style"=>"display: inline-block; background:none; border-radius:0px; color: #fff; text-align:left; font-size:14px; padding:5px 8px; padding-left:5px; margin-left:0px; line-height:16px; border:solid 0px #fff; text-decoration:underline; font-weight:600;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block; background:#1c1a1a; border-radius:14px; color:#4b4949; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; line-height:16px; border:solid 0px #fff;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; background:none; border-radius:14px; color:#4b4949; text-align: center; font-size: 12px; padding:3px 6px; margin-left:10px; line-height:16px; border:solid 1px #1c1a1a;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #fff; color: #fff; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display: inline-block; background:#fff; border-radius:14px; color: #4b4949; text-align: center; font-size: 12px; padding:5px 8px; margin-left:10px; line-height:16px; border:solid 0px #fff;"
		),
	)
);

$cli_themes_banner=array(
	'theme_banner_1'=>$cli_theme_banner1,
	'theme_banner_2'=>$cli_theme_banner2,
	'theme_banner_3'=>$cli_theme_banner3,
	'theme_banner_4'=>$cli_theme_banner4,
	'theme_banner_5'=>$cli_theme_banner5,
	'theme_banner_6'=>$cli_theme_banner6,
	'theme_banner_7'=>$cli_theme_banner7,
	'theme_banner_8'=>$cli_theme_banner8,
	'theme_banner_9'=>$cli_theme_banner9,
	'theme_banner_10'=>$cli_theme_banner10,
	'theme_banner_11'=>$cli_theme_banner11,

);