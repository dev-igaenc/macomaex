
<?PHP

		// - 팝업 ---
		//echo "<div ID='event_popup' style='POSITION: absolute;left: 0;HEIGHT: 0px;TOP:0px; width:1000px; Z-INDEX: 999999;display:block;'>";
		echo "<div ID='event_popup' style='POSITION: absolute; left : 50%; transform : translatex(-50%); TOP:120px; width:1200px; Z-INDEX: 1000;display:block;'>";


				
		$query = "select * from nb_popup where sitekey = '$NO_SITE_UNIQUE_KEY' and p_loc = 'P'  and p_view='Y' and ( (p_sdate <= CURDATE() and p_edate >= CURDATE() ) or p_none_limit = 'Y' ) ORDER BY p_idx asc , no desc";
		//echo $query;
		$result4th =mysqli_query($query);
		$arrPopup = array(); 
		while($row = mysqli_fetch_assoc($result4th)) { 
			$arrPopup[] = $row; 
		} 



		foreach( $arrPopup as $k=>$v ){

			if( $_COOKIE["AuthPopupClose_" . $v['no']] <> "Y" ) {
				$_img = $UPLOAD_WDIR_POPUP."/" . $v['p_img'];
				$_s = @getimagesize($_img);
				$app_div_name = "event_popup_div_" . $v['no'];

				echo "
						<div id='". $app_div_name ."'>
							<div class='popup' style='POSITION: absolute;Z-INDEX: 1000; LEFT:".$v['p_left']."px;TOP:".$v['p_top']."px;' class='". $app_div_name ."'>
								<div class='img'>
									<A HREF='".$v['p_link']."' target='".$v['p_target']."'><img src='". $_img ."' alt='".$v['p_title']."' style='width : 100%;'/></a>
								</div>
								<div class='btn_area' >
							
									<input type=\"checkbox\" id=\"agree_check".$v['no']."\"  class='no_checkbox' onclick=\"common_frame.location.href=('./inc/lib/popup.close.php?_mode=popup_close&uid=".$v['no']."')\" >
									<label for=\"agree_check".$v['no']."\"'>
									
										<span class='no_checkbox_common_txt'>오늘하루 창닫기</span>
									</label>		
								

									<a href=\"javascript:popup_display('". $app_div_name ."')\" title='닫기' class='btn_close' style='margin:0px!important'><img src='/resource/images/popup_close_btn.png' alt='닫기' width='25'/></a>
								
								</div>
							</div>
						</div>

				";
			}
		}

		echo "</div>";
		// - 팝업 ---
?>

<script language="javascript">

	function popup_display( divname ){
		$("#" + divname).css("display" , "none");
	}

</script>
<!-- 팝업 -->
