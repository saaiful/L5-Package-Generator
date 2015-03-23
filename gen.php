<?php
function cliInput($text,$next = '')
{
	echo $text;
	$handle = fopen ("php://stdin","r");
	$re = str_replace(["\r", "\n"],'',fgets($handle));
	echo $next;
	return $re;
}

function newAuthInfo()
{
	$author['name'] = cliInput('Author Name : ');
	$author['email'] = cliInput('Author Email : ');
	file_put_contents("author.json", json_encode($author));
	echo "New Author Info Saved!\n";
}

function makeDir($path)
{
	echo "Creating ".$path;
	if(!is_dir($path)) { mkdir($path); }
	if(is_dir($path)){ file_put_contents($path."/.gitkeep", ''); echo " :: Done\n"; }
}

function newPkg()
{
	$pkg['vendor'] = cliInput("Package Vendor : ");
	$pkg['name'] = cliInput("Package Name : ");
	$pkg['description'] = cliInput("Package Description : ");
	$pkg['keywords'] = cliInput("Package Keywords : ");
	$pkg['license'] = cliInput("Package License : ");
	// $pkg['require'] = cliInput("Package Require : ");
	// $pkg['require-dev'] = cliInput("Package Require-dev : ");
	// 
	makeDir(__DIR__."/".$pkg['vendor']);
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']);
	echo shell_exec("cd ".__DIR__."/".$pkg['vendor']."/".$pkg['name']." && git init");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/tests");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/config");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/controllers");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/translations");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/migrations");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/views");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/models");
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/".ucfirst($pkg['vendor']));
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/".ucfirst($pkg['vendor'])."/".ucfirst($pkg['name']));
	makeDir(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/".ucfirst($pkg['vendor'])."/".ucfirst($pkg['name'])."/Facades");

	// Composer
	$temp = file_get_contents(__DIR__."/template/composer");
	$author = json_decode(@file_get_contents('author.json'),true);
	$keywords = explode(",", $pkg['keywords']);
	$find = ["<<PKGNAME>>", "<<PKGDESC>>", "<<LIC>>", "<<KEY>>", "<<AUTHNAME>>", "<<AUTHEMAIL>>", "<<REQ>>", "<<DEV>>", "<<AL>>"];
	$repl = [$pkg['vendor']."/".$pkg['name'], $pkg['description'], $pkg['license'], json_encode($keywords), $author['name'], $author['email'], $pkg['require'], $pkg['require-dev'], ucfirst($pkg['vendor'])."\\\\".ucfirst($pkg['name'])];
	$temp = str_replace($find , $repl, $temp);
	file_put_contents(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/composer.json", $temp);
	echo "composer.json created\n";

	// ServiceProvider
	$temp = file_get_contents(__DIR__."/template/ServiceProvider");
	$find = ["<<UCPKGPATH>>","<<CLASS>>","<<PKG>>","<<PKGNAME>>"];
	$repl = [ucfirst($pkg['vendor'])."\\".ucfirst($pkg['name']),ucfirst($pkg['name']),strtolower($pkg['name']),$pkg['name']];
	$temp = str_replace($find , $repl, $temp);
	file_put_contents(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/".ucfirst($pkg['vendor'])."/".ucfirst($pkg['name'])."/".ucfirst($pkg['name'])."ServiceProvider.php", $temp);
	echo "ServiceProvider created\n";

	// Main Class
	$temp = file_get_contents(__DIR__."/template/class");
	$find = ["<<UCPKGPATH>>","<<CLASS>>"];
	$repl = [ucfirst($pkg['vendor'])."\\".ucfirst($pkg['name']),ucfirst($pkg['name'])];
	$temp = str_replace($find , $repl, $temp);
	file_put_contents(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/".ucfirst($pkg['vendor'])."/".ucfirst($pkg['name'])."/".ucfirst($pkg['name']).".php", $temp);
	echo "Main Class created\n";

	// Main Class
	$temp = file_get_contents(__DIR__."/template/facade");
	$find = ["<<UCPKGPATH>>","<<CLASS>>","<<NAME>>"];
	$repl = [ucfirst($pkg['vendor'])."\\".ucfirst($pkg['name']),ucfirst($pkg['name']),$pkg['name']];
	$temp = str_replace($find , $repl, $temp);
	file_put_contents(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/".ucfirst($pkg['vendor'])."/".ucfirst($pkg['name'])."/Facades"."/".ucfirst($pkg['name']).".php", $temp);
	echo "Facade Class created\n";


	// Config
	file_put_contents(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/src/config/config.php", file_get_contents(__DIR__."/template/config"));


	// ReadMe.md
	$temp = file_get_contents(__DIR__."/template/readme");
	$find = ["<<UNAME>>","<<DESC>>","<<UCPKG>>",'<<PKGPATH>>'];
	$repl = [ucfirst($pkg['name']), $pkg['description'], ucfirst($pkg['vendor'])."\\".ucfirst($pkg['name']), $pkg['vendor'].'/'.$pkg['name']];
	$temp = str_replace($find , $repl, $temp);
	file_put_contents(__DIR__."/".$pkg['vendor']."/".$pkg['name']."/readme.md", $temp);
	echo "Readme created\n";


}
// Intro
echo "Laravel 5 Package Generator\n";
echo "Author: Saiful Islam\n";
echo "http://github.com/saaiful\n";
echo "@saaiful\n";
echo "http://fb.com/infosaifulislam\n\n";

// Check Author Info
$author = json_decode(@file_get_contents('author.json'),true);
if(empty($author['name']) && empty($author['email']))
{
	newAuthInfo();
}

// Ask For Command
$command = cliInput(">");

switch ($command) {
	case 'pkg':
		newPkg();
		break;
	
	default:
		echo "Command not found!";
		break;
}
?>