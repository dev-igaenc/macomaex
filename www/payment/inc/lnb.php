<? 
    $depthText = ""; 
    $lnbText = ""; 
    
    if($depthNum == 1){
        $depthText = $depthText_1; 
        
        if($lnbNum == 1){
            $lnbText = $menu_1_1;
        } else if($lnbNum == 2){
            $lnbText = $menu_1_2;
        } 
    }
    if($depthNum == 2){
        $depthText = $depthText_2; 
        
        if($lnbNum == 1){
            $lnbText = $menu_2_1;
            
        } else if($lnbNum == 2){
            $lnbText = $menu_2_2;
            
        } else if($lnbNum == 3){
            $lnbText = $menu_2_3;
            
        } else if($lnbNum == 4){
            $lnbText = $menu_2_4;
            
        } else if($lnbNum == 5){
            $lnbText = $menu_2_5;
        } 
    }
    if($depthNum == 3){
        $depthText = $depthText_3; 
        
        if($lnbNum == 1){
            $lnbText = $menu_3_1;
            
        } else if($lnbNum == 2){
            $lnbText = $menu_3_2;
            
        } else if($lnbNum == 3){
            $lnbText = $menu_3_3;
        } 
    }
    if($depthNum == 4){
        $depthText = $depthText_4; 
        
        if($lnbNum == 1){
            $lnbText = $menu_4_1;
            
        } else if($lnbNum == 2){
            $lnbText = $menu_4_2;
            
        } else if($lnbNum == 3){
            $lnbText = $menu_4_3;
            
        } else if($lnbNum == 4){
            $lnbText = $menu_4_4;
            
        } else if($lnbNum == 5){
            $lnbText = $menu_4_5;
        } 
    }
    if($depthNum == 5){
        $depthText = $depthText_5; 
        
        if($lnbNum == 1){
            $lnbText = $menu_5_1;
            
        } else if($lnbNum == 2){
            $lnbText = $menu_5_2;
            
        } else if($lnbNum == 3){
            $lnbText = $menu_5_3;
            
        } else if($lnbNum == 4){
            $lnbText = $menu_5_4;
            
        }
    }
    
    if($member){
        $depthText = $depthText_member;
        if($memberNum == 1){
            $lnbText = $member_login; 
        } else if ($memberNum == 2){
            $lnbText =  $member_signup; 
        } else if ($memberNum == 3){
            $lnbText =  $member_profile; 
        } 
    }

    // echo 'boardNo :'.$board_no; 
    // echo 'depthNum :'.$depthNum; 
    // echo 'depthText :'.$depthText;
    // echo 'lnbNum :'.$lnbNum;
    // echo 'lnbText :'.$lnbText;
?>


<aside class="no-idc">
    <div class="no-full-wrapper">
        <div class="no-idc__text">
            <span><?=$depthText?></span>
            <span><?=$lnbText?></span>
            
            <?
                if($isView && $view_skin === 'caseStudy'){ ?>
                    <span class="view-text"><?=$data[extra1]?></span>
                <?}
            ?>
        </div>
    </div>
</aside>
