<?


function getBoardInfoByName($nm){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select  a.no, a.sitekey, a.title, a.skin, a.top_banner_image, a.contents, a.view_yn, a.secret_yn, a.sort_no, a.list_size, a.fileattach_yn, a.fileattach_cnt, a.comment_yn, a.depth1, a.depth2, a.depth3, a.lnb_path, a.extra_match_field2,
						a.skin_view
						from nb_board_manage  a
					where a.sitekey = '$NO_SITE_UNIQUE_KEY' and a. title = '$nm'";
			
	$result=mysqli_query($connect ,$query);

	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;

}



function getBoardInfoByNo($board_no){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select  a.no, a.sitekey, a.title, a.skin, a.top_banner_image, a.contents, a.view_yn, a.secret_yn, a.sort_no, a.list_size, a.fileattach_yn, a.fileattach_cnt, a.comment_yn, a.depth1, a.depth2, a.depth3, a.lnb_path, a.skin_view
						from nb_board_manage  a
					where a.sitekey = '$NO_SITE_UNIQUE_KEY' and a. no = $board_no";
	//echo $query;		
	$result=mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;
}


function getBoardManageInfoByNo($board_no){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select  sitekey, title, skin, regdate, top_banner_image, contents, view_yn, secret_yn, 
						list_size, fileattach_yn, fileattach_cnt, comment_yn, depth1, depth2, depth3, lnb_path, category_yn, extra_match_field1, extra_match_field2, extra_match_field3, extra_match_field4, extra_match_field5, extra_match_field6,
						extra_match_field7, extra_match_field8, extra_match_field9, extra_match_field10, extra_match_field11, extra_match_field12, extra_match_field13, extra_match_field14, extra_match_field15
						skin_view
						from nb_board_manage 
					where sitekey = '$NO_SITE_UNIQUE_KEY' and no = $board_no";
			
	$result=mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;
}


function getBoardLimit($board_no, $limit=5, $orderby){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$arr = array();
	$mainqry = " where a.sitekey = '$NO_SITE_UNIQUE_KEY' and a.board_no = $board_no";
	$orderByqry = "a.is_notice='Y' desc , a.regdate desc";
	if($orderby){
		$orderByqry = $orderby;
	}
	$query = " select a.no, a.board_no, a.user_no, a.category_no, a.comment_cnt, a.title, a.contents, a.regdate, a.read_cnt, a.thumb_image, 
					a.is_admin_writed, a.is_notice, a.is_secret, a.secret_pwd, a.write_name, a.isFile
					 ,a.direct_url, file_attach_1, file_attach_origin_1 ,file_attach_2,file_attach_origin_2 ,file_attach_3, file_attach_origin_3 ,file_attach_4, file_attach_origin_4 ,file_attach_5, file_attach_origin_5, a.extra1, a.extra2, a.extra3, a.extra4, a.extra5, 
					 a.extra6, a.extra7, a.extra8, a.extra9, a.extra10, a.extra11, a.extra12, a.extra13, a.extra14, a.extra15,
					 b.title as board_name
					from nb_board a
					left join nb_board_manage b on a.board_no = b.no
					$mainqry  order by  $orderByqry  limit 0, " . $limit ;
	//echo $query;
	$result =mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;
}


function getBoardRole($board_no, $user_lev){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select no, lev_no, role_write, role_edit, role_view, role_list, role_delete, role_comment from nb_board_lev_manage where sitekey = '$NO_SITE_UNIQUE_KEY' and lev_no = $user_lev and board_no = $board_no";
	$result =mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;
}



function getBoardCategory($board_no){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select no, name from nb_board_category where sitekey = '$NO_SITE_UNIQUE_KEY' and board_no = $board_no order by sort_no asc";
	$result =mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;

}

function getBoardCnt($board_no){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select count(no) as cnt from nb_board where board_no = $board_no";
	$result =mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;

}

function getBoardCategoryCnt($category_no){

	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "select count(no) as cnt from nb_board where category_no = $category_no";
	$result =mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;

}



function show($stuff){
  echo "<pre>";
  var_dump($stuff);
  echo "</pre>";
}


function getBoardInfoWithCategory($name, $category_no){
	global $NO_SITE_UNIQUE_KEY, $connect;
	$query = "SELECT  
						a.no, 
						a.sitekey, 
						a.title, 
						a.skin, 
						a.top_banner_image, 
						a.contents, 
						a.view_yn, 
						a.secret_yn, 
						a.sort_no, 
						a.list_size, 
						a.fileattach_yn, 
						a.fileattach_cnt, 
						a.comment_yn, 
						a.depth1, 
						a.depth2, 
						a.depth3, 
						a.lnb_path, 
						a.view_skin,
						b.thumb_image,
						b.category_no,
						b.title,
						b.extra1,
						b.extra2,
						b.extra3,
						b.extra4,
						b.extra5,
						b.extra6
					FROM nb_board_manage  AS a
					LEFT JOIN nb_board AS b
					ON a.no = b.board_no
					WHERE 
						a.sitekey = '$NO_SITE_UNIQUE_KEY' 
						and a.title = '$name'
						and b.category_no = $category_no
					ORDER BY b.no DESC
					LIMIT 4";
			
	$result=mysqli_query($connect ,$query);
	$r = array(); 
	while($row = mysqli_fetch_assoc($result)) { 
		$r[] = $row; 
	} 
	if($result){
		mysqli_free_result( $result );
	}
	return $r;

}



?>


