<?php
//button_1 : Accept [cookie_button]
//button_2 : Read More [cookie_link]
//button_3 : Reject [cookie_reject]
//button_4 : Settings [cookie_settings]
//button_5 : Close [cookie_close]

//note : always add empty border when adding styles Eg: solid 0px #ccc;

//always follow the naming structure for other bar types Eg: cli_theme_{bar type}_default
//pls add width param for preview in default themes eg: width:100%; for banner type.
$cli_theme_widget_default=array
(
	'title'=>'Theme_widget current',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'',
			"style"=>"padding:46px; box-sizing:border-box; float:left; width:445px; border:solid 0px #fff; font-size:18px;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"padding:5px 0px; color:#fff; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; padding:8px 16px 8px; border-radius:4px; text-align:center; font-size: 12px; border:solid 0px #fff;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Read More",
			"style"=>"display:inline-block; text-align:center; font-size:12px; padding:8px 16px 8px; border:solid 0px #fff;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; padding:8px 16px 8px; border-radius:4px; text-align:center; font-size: 12px; border:solid 0px #fff;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display:inline-block; padding:8px 16px 8px; border-radius:4px; text-align:center; font-size: 12px; border:solid 0px #fff;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>1,
			"text"=>"Accept All",
			"style"=>"padding:46px; box-sizing:border-box; float:left; width:445px; border:solid 0px #fff; font-size:18px;"
		),
	)
);



$cli_theme_widget1=array
(
	'title'=>'Theme_widget1',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'<div class="cli-bar-container cli-style-v2"><div class="cli-bar-message">We use cookies on our website to give you the most relevant experience by remembering your preferences and repeat visits. By clicking “Accept”, you consent to the use of ALL the cookies. However you may visit Cookie Settings to provide a controlled consent.</div><div class="cli-bar-btn_container">[cookie_settings][cookie_button]</div></div>',
			"style"=>"background-color:#ffffff;padding: 13px 20px;text-align:left;width:445px; box-sizing:border-box;"
		),
		"heading"=>array(
			"status"=>0,
			"text"=>"This website uses cookies",
			"style"=>"font-size: 16px;margin: 10px 0;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"margin: 5px 5px 5px 30px; border-radius: 0px; padding: 8px 25px; color: rgb(255, 255, 255); background-color: rgb(97, 162, 41);"
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
			"style"=>"border-bottom: 1px solid; color: rgb(137, 136, 136);"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display: inline-block; background:none; border-radius:20px; border:solid 1px #fff; color: #fff; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-15px; margin-top:-15px; float:right; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"margin: 5px 5px 5px 30px; border-radius: 0px; padding: 8px 25px; color: rgb(255, 255, 255); background-color: rgb(97, 162, 41);"
		),
	)
);
$cli_theme_widget2=array
(
	'title'=>'Theme_widget2',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'<div class="wt-cli-template cli-style-v3">We use cookies in our website to give you the most relevant experience by remembering your preferences and repeat visits. By clicking “Accept”, you consent to the use of the cookies explicitly. Visit Cookie Settings to know more about the cookies used on our website.<div class="cli-bar-actions">[wt_cli_category_widget]<div class="cli-bar-btn_container">[cookie_settings][cookie_button]</div></div></div>',
			"style"=>"background-color:#ffffff;text-align:left;width:445px; box-sizing:border-box;"
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
			"style"=>"border-bottom: 1px solid; color: rgb(137, 136, 136);"
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
$cli_theme_widget3=array
(
	'title'=>'Theme_widget3',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'This website uses cookies to ensure you get the best experience on our website.
			[cookie_link][cookie_settings] 
			[cookie_reject][cookie_button]',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; background:#421fa9; color:#fff; font-size: 16px; line-height: 24px; border:solid 0px #fff; text-align:left; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#fff; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#fff; background:#f90071; text-align: center; border:solid 0px #fff; margin-top:30px; border-radius:0px; line-height:28px; font-size: 16px; margin-left:3%; font-weight:bold;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display: inline-block;color: #fff; text-decoration: underline; font-weight:bold; border:solid 0px #fff; line-height:28px; font-size: 16px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Decline",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#fff; text-align: center; border:solid 0px #fff; margin-top:30px; line-height: 28px; font-size: 16px; background:#552fcb; font-weight:bold;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block;color: #fff; text-decoration: underline; font-weight:bold; border:solid 0px #fff; line-height:28px; font-size: 16px; margin-left:10px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#fff; background:#f90071; text-align: center; border:solid 0px #fff; margin-top:30px; border-radius:0px; line-height:28px; font-size: 16px; margin-left:3%; font-weight:bold;"
		),
	)
);
$cli_theme_widget4=array
(
	'title'=>'Theme_widget4',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'This website uses cookies to ensure you get the best experience on our website.
			[cookie_link]
			[cookie_reject][cookie_settings]
			[cookie_button]',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; background:#010101; color:#fff; font-size: 16px; line-height:24px; border:solid 0px #fff; text-align:left; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#fff; background:none; text-align:left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:8px 10px; color:#f1d500; background:#010101; text-align:center; border:solid 2px #f1d500; margin-top:8px; font-weight:600; line-height:28px; font-size:16px; border-radius:0px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display: inline-block;color: #fff; text-decoration: underline; font-weight:600; border:solid 0px #fff; margin-top:8px; font-size:16px; border-radius:0px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; color: #fff; background:#1e1e1e; text-decoration:none; font-weight:600; border:solid 0px #fff; width:48%; text-align:center; padding:6px 8px; margin-right:3%; box-sizing:border-box; margin-top:8px; font-size:16px; line-height:28px; border-radius:0px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block;color: #fff; text-decoration:none; font-weight:600; border:solid 1px #0f0f0f; padding:6px 8px; background:#010101; width:48%; box-sizing:border-box; margin-top:8px; font-size:16px; text-align:center; line-height:28px; border-radius:0px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:8px 10px; color:#f1d500; background:#010101; text-align:center; border:solid 2px #f1d500; margin-top:8px; font-weight:600; line-height:28px; font-size:16px; border-radius:0px;"
		),
	)
);
$cli_theme_widget5=array
(
	'title'=>'Theme_widget5',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'This website uses cookies to ensure you get the best experience on our website.
			[cookie_link]
			[cookie_button][cookie_reject][cookie_settings]',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; background:#8e3a2d; color:#fff; font-size: 16px; line-height: 24px; border-radius:4px; text-align:left; border:solid 0px #fff; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#fff; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; width:30%; padding:8px 10px; color:#000; background:#63ffaa; text-align: center; border-radius:4px; border:solid 0px #fff; margin-top:15px; line-height:28px; font-size:14px; margin-right:1.5%; font-weight:bold;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display:inline-block; color:#fff; text-decoration: underline; font-weight:bold; border:solid 0px #fff; padding:10px 0px; line-height:28px; font-size:14px; border-radius:0px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; box-sizing:border-box; width:30%; padding:8px 10px; color:#fff; background:#a85b51; text-align:center; border:solid 0px #fff; margin-top:15px; line-height:28px; font-size:14px; border-radius:4px; margin-right:1.5%; font-weight:bold;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display:inline-block; color:#fff; text-decoration:none; font-weight:bold; border:solid 1px #fff; padding:6px 8px; background:none; line-height:28px; font-size:14px; border-radius:4px; margin-right:1.5%; width:30%; text-align:center; margin-top:15px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; width:30%; padding:8px 10px; color:#000; background:#63ffaa; text-align: center; border-radius:4px; border:solid 0px #fff; margin-top:15px; line-height:28px; font-size:14px; margin-right:1.5%; font-weight:bold;"
		),
	)
);
$cli_theme_widget6=array
(
	'title'=>'Theme_widget6',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'This website uses cookies to ensure you get the best experience on our website.
			[cookie_link]
			[cookie_button]
			[cookie_reject][cookie_settings]
			',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; background:#010101; color:#dcdcdc; font-size: 16px; line-height:24px; border:solid 0px #fff; text-align:left; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#fff; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:8px 10px; color:#000; background:#f3da00; text-align:center; border:solid 0px #fff; margin-top:15px; line-height:28px; font-size:16px; font-weight:600;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display:inline-block;color:#d1d0d0; text-decoration: underline; font-weight:600; border:solid 0px #fff; margin-top:8px; font-size:16px; margin-top:10px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display: inline-block;color: #fff; text-decoration:none; font-weight:600; border:solid 0px #fff; background:#1e1e1e; line-height:28px; font-size:16px; box-sizing:border-box; padding:8px 10px; width:48%; margin-right:3%; text-align:center; margin-top:15px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block;color: #fff; text-decoration:none; font-weight:600; border:solid 1px #0f0f0f; line-height:28px; font-size:16px; background:#010101; box-sizing:border-box; padding:8px 10px; width:48%; text-align:center; margin-top:15px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:8px 10px; color:#000; background:#f3da00; text-align:center; border:solid 0px #fff; margin-top:15px; line-height:28px; font-size:16px; font-weight:600;"
		),
	)
);
$cli_theme_widget7=array
(
	'title'=>'Theme_widget7',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'This website uses cookies to ensure you get the best experience on our website.
			[cookie_link][cookie_settings]
			[cookie_reject][cookie_button]',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; background: #010101; color:#fff; font-size: 16px; line-height:24px; text-align:left; font-weight:400; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#fff; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#010101; background:#f3da00; text-align:center; border-radius:6px; font-size:18px; border:solid 0px #fff; line-height:28px; font-size:18px; margin-top:20px; font-weight:600;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display: inline-block;color: #fff; text-decoration: underline; font-weight:600; border:solid 0px #fff; margin-right:15px; margin-top:15px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#ffffff; background:#1e1e1e; text-align:center; border-radius:6px; font-size:18px; border:solid 0px #fff; line-height:28px; font-size:18px; margin-top:20px; font-weight:600; margin-right:3%;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block;color: #fff; text-decoration: underline; font-weight:600; border:solid 0px #fff; margin-top:15px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#010101; background:#f3da00; text-align:center; border-radius:6px; font-size:18px; border:solid 0px #fff; line-height:28px; font-size:18px; margin-top:20px; font-weight:600;"
		),
	)
);
$cli_theme_widget8=array
(
	'title'=>'Theme_widget8',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'This website uses cookies to ensure you get the best experience on our website.
			[cookie_link]
			[cookie_button][cookie_reject][cookie_settings]',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; background: #fff; color:#0e0e0e; font-size: 16px; line-height:24px; border:solid 1px #dbdbdb; text-align:left; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#0e0e0e; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; padding:8px 10px; color:#fff; background:#00432c; text-align:center; font-size:13px; text-transform:uppercase; font-weight:600; border:solid 0px #fff; line-height:28px; border-radius:0px; margin-top:20px; width:29%; margin-right:2%;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display: inline-block;color: #fff; text-decoration:underline; color:#0e0e0e; font-weight:600; border:solid 0px #fff; font-size:16px; margin-top:10px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; box-sizing:border-box; padding:8px 10px; color:#fff; background:#b9bfbe; text-align:center; font-size:13px; text-transform:uppercase; font-weight:600; border:solid 0px #fff; line-height:28px; border-radius:0px; margin-top:20px; width:29%; margin-right:2%;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display:inline-block;color:#fff; text-decoration:none; color:#0e0e0e; border:solid 1px #b9bfbe; width:29%; padding:8px 10px; margin-top:20px; text-transform:uppercase; text-align:center; font-size:13px; line-height:28px; box-sizing:border-box; color:#b9bfbe; font-weight:600;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; padding:8px 10px; color:#fff; background:#00432c; text-align:center; font-size:13px; text-transform:uppercase; font-weight:600; border:solid 0px #fff; line-height:28px; border-radius:0px; margin-top:20px; width:29%; margin-right:2%;"
		),
	)
);
$cli_theme_widget9=array
(
	'title'=>'Theme_widget9',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'This website uses cookies to ensure you get the best experience on our website.
			[cookie_link][cookie_settings]
			[cookie_reject][cookie_button]',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; background:#e9f6f8; color:#7aa0a3; font-size: 16px; line-height:24px; text-align:center; border:solid 1px #57cadb; text-align:left; font-weight:300; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#7aa0a3; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#fff; background:#57cadb; text-align: center; border-radius:4px; font-weight:700; border:solid 0px #ccc; margin-top:10px; line-height:28px; font-size:16px; margin-left:3%;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display: inline-block; color:#7aa0a3; text-decoration:underline; font-weight:600; border:solid 0px #ccc; font-size:16px; margin-top:15px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#ffffff; background:#cbe8ea; text-align: center; border-radius:4px; font-weight:700; border:solid 0px #ccc; font-size:16px; line-height:28px; margin-top:10px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display: inline-block; color:#7aa0a3; text-decoration: underline; font-weight:600; border:solid 0px #ccc; margin-left:10px; font-size:16px; margin-top:15px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; width:48%; padding:8px 10px; color:#fff; background:#57cadb; text-align: center; border-radius:4px; font-weight:700; border:solid 0px #ccc; margin-top:10px; line-height:28px; font-size:16px; margin-left:3%;"
		),
	)
);
$cli_theme_widget10=array
(
	'title'=>'Theme_widget10',
	'config'=>array(
		"bar"=>array(
			"type"=>'widget',
			"text"=>'[cookie_close]This website uses cookies to ensure you get the best experience on our website.
			[cookie_link]
			[cookie_reject][cookie_settings]
			[cookie_button]',
			"style"=>"width:445px; box-sizing:border-box; padding:46px; padding-bottom:0px; background:#aa0001; color:#fff; font-size:16px; line-height:24px; text-align:left; text-transform:none; letter-spacing:normal;"
		),
		"heading"=>array(
			"status"=>1,
			"text"=>"This website uses cookies",
			"style"=>"display:inline-block; box-sizing:border-box; width:100%; padding:5px 0px; color:#fff; background:none; text-align: left; border:solid 0px #fff; margin:0px; margin-bottom:5px; border-radius:0px; line-height:24px; font-size:18px; font-weight:bold; text-transform:none; letter-spacing:normal;"
		),
		"button_1"=>array(
			"status"=>1,
			"text"=>"Accept",
			"style"=>"display:inline-block; box-sizing:border-box; width:445px; padding:12px 0px; color:#fff; background:#ff0000; text-align: center; font-weight:600; border:solid 0px #ccc; margin-top:20px; margin-left:-46px; line-height:28px; font-size:18px; border-radius:0px;"
		),
		"button_2"=>array(
			"status"=>1,
			"text"=>"Learn More",
			"style"=>"display:inline-block; color:#fff; text-decoration: underline; font-weight:600; border:solid 0px #ccc; margin-top:15px; font-size:16px; border-radius:0px;"
		),
		"button_3"=>array(
			"status"=>1,
			"text"=>"Reject",
			"style"=>"display:inline-block; color:#fff; text-decoration:none; font-weight:600; border:solid 0px #ccc; width:48%; box-sizing:border-box; text-align:center; background:#ff2c36; font-size:16px; line-height:24px; padding:8px 0px; margin-top:15px; margin-right:3%; border-radius:0px;"
		),
		"button_4"=>array(
			"status"=>1,
			"text"=>"Settings",
			"style"=>"display:inline-block; color:#fff; text-decoration:none; font-weight:600; border:solid 1px #ff0000; width:48%; box-sizing:border-box; text-align:center; background:#aa0001; font-size:16px; line-height:24px; padding:6px 0px; margin-top:15px; border-radius:0px;"
		),
		"button_5"=>array(
			"status"=>1,
			"text"=>"X",
			"style"=>"display:inline-block; background:none; border-radius:20px; border:solid 1px #ccc; color: #ccc; text-align: center; font-size: 12px; width:22px; height: 22px; line-height: 22px; margin-right:-22px; margin-top:-24px; float: right; position:relative; cursor:pointer;"
		),
		"button_7"=>array(
			"status"=>0,
			"text"=>"Accept All",
			"style"=>"display:inline-block; box-sizing:border-box; width:445px; padding:12px 0px; color:#fff; background:#ff0000; text-align: center; font-weight:600; border:solid 0px #ccc; margin-top:20px; margin-left:-46px; line-height:28px; font-size:18px; border-radius:0px;"
		),
	)
);
//follow the bellow naming format. it is using in preview section.
$cli_themes_widget=array(
	'theme_widget_1'=>$cli_theme_widget1,
	'theme_widget_2'=>$cli_theme_widget2,	
	'theme_widget_3'=>$cli_theme_widget3,
	'theme_widget_5'=>$cli_theme_widget5,
	'theme_widget_6'=>$cli_theme_widget6,
	'theme_widget_7'=>$cli_theme_widget7,
	'theme_widget_8'=>$cli_theme_widget8,
	'theme_widget_9'=>$cli_theme_widget9,
	'theme_widget_10'=>$cli_theme_widget10,
	'theme_widget_4'=>$cli_theme_widget4,	
);


