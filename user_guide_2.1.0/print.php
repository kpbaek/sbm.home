<?php
include_once ("index.html");
include_once ("./general/requirements.html");
include_once ("./license.html");
include_once ("./general/credits.html");

//하위폴더 배열
$forder = array('installation','overview','general','libraries','helpers');

//하위폴더 파일별로 출력
foreach($forder as $forder){
	file_list("./".$forder);
}

function file_list($BaseDir) {
	$handle = opendir( $BaseDir );
	while( $file = readdir( $handle ) ) {
		if( $file == "." || $file == "..") continue;
		include_once ($BaseDir."/".$file);
	}
}
?>