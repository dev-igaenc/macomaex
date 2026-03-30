

$(document).ready(function(){
	var mode = $("#mode").val();
	if (mode.indexOf("save") != -1) {
		getCategory('big', '', '');
		cbig = $("#cbig").val();
		cmid = $("#cmid").val();
		getCategoryManual('sml', 'career1_', cbig,cmid,0,0);
		getCategoryManual('sml', 'career2_', cbig,cmid,0,0);
		getCategoryManual('sml', 'career3_', cbig,cmid,0,0);
	}
});

function getCategory(kind, v, gb){

	var big = 0;
	var mid = 0;
	var sml = 0;
	var itm = 0;

	if(gb == null || gb == "")
		gb = "";

	var reqParam = "";

	if(kind == "big"){

		$("#"+gb+"category_big option").remove();

	}else if(kind == "mid") {

		$("#"+gb+"category_mid option").remove();

		big = $("#"+gb+"category_big").val();
		big = doCategoryExtraction(big);

	}else if(kind == "sml") {

		$("#"+gb+"category_sml option").remove();

		big = $("#"+gb+"category_big").val();
		mid = $("#"+gb+"category_mid").val();

		big = doCategoryExtraction(big);
		mid = doCategoryExtraction(mid);

	}else if(kind == "itm") {

		$("#"+gb+"category_itm option").remove();
		
		if($("#cbig").val() != ""){
			big = $("#cbig").val();
		}else{
			big = $("#"+gb+"category_big").val();
			big = doCategoryExtraction(big);
		}

		if($("#cmid").val() != ""){
			mid = $("#cmid").val();
		}else{
			mid = $("#"+gb+"category_mid").val();
			mid = doCategoryExtraction(mid);
		}

		sml = $("#"+gb+"category_sml").val();
		sml = doCategoryExtraction(sml);

	} 

	$.ajax({
		type:"POST",
		url:"./ajax/process.php",
		cache: false,
		data:"kind="+kind+"&big="+big+"&mid="+mid+"&sml="+sml+"&mode=getCategory",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "success"){
				
				
					var title = "";
					if(kind == "big")
						title = "대분류 선택";
					else if(kind == "mid")
						title = "업종 선택";
					else if(kind == "sml")
						title = "소업종 선택";
					else if(kind == "itm")
						title = "아이템 선택";

					$("#"+gb+"category_"+kind).append("<option value=''>"+title+" 선택</option>");
					$.each(jsonData.rows, function(key,state){
						var disp = state.disp;
						var viewDisp = "on";
						if(disp == "Y"){
							$("#"+gb+"category_"+kind).append("<option value="+state.no+"^"+state.subject+">"+state.subject+"</option>");
						}
					});


				

				

			}else if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}
		},
		error:function(a,s){
			alert("err : "+s);
		},
		complete : function()
	   {
	   }
	}); 
}


function getCategoryManual(kind, gb, cbig, cmid, csml, citm){

	var big = 0;
	var mid = 0;
	var sml = 0;
	var itm = 0;

	if(gb == null || gb == "")
		gb = "";

	var reqParam = "";

	if(kind == "big"){

		$("#"+gb+"category_big option").remove();

	}else if(kind == "mid") {

		$("#"+gb+"category_mid option").remove();

		if(cbig > 0){
			big = cbig;
		}else{
			big = $("#"+gb+"category_big").val();
			big = doCategoryExtraction(big);
		}
		

	}else if(kind == "sml") {

		$("#"+gb+"category_sml option").remove();
		
		if(cbig > 0){
			big = cbig;
		}else{
			big = $("#"+gb+"category_big").val();
			big = doCategoryExtraction(big);
		}

		if(cmid > 0){
			mid = cmid;
		}else{
			mid = $("#"+gb+"category_mid").val();
			mid = doCategoryExtraction(mid);
		}
	
		


	}

	$.ajax({
		type:"POST",
		url:"./ajax/process.php",
		cache: false,
		data:"kind="+kind+"&big="+big+"&mid="+mid+"&sml="+sml+"&mode=getCategory",
		dataType:"html",
		success:function(data){
			var jsonData = $.parseJSON(data);
			if(jsonData.result == "success"){
				
				if(kind == "itm"){
				
				}else{
					var title = "";
					if(kind == "big")
						title = "대분류 선택";
					else if(kind == "mid")
						title = "업종 선택";
					else if(kind == "sml")
						title = "소업종 선택";
					else if(kind == "itm")
						title = "아이템 선택";

					$("#"+gb+"category_"+kind).append("<option value=''>"+title+" 선택</option>");
					$.each(jsonData.rows, function(key,state){
						var disp = state.disp;
						var viewDisp = "on";
						if(disp == "Y"){
							$("#"+gb+"category_"+kind).append("<option value="+state.no+"^"+state.subject+">"+state.subject+"</option>");
						}
					});


				}

				

			}else if(jsonData.result == "fail"){
				alert(jsonData.msg);
			}
		},
		error:function(a,s){
			alert("err : "+s);
		},
		complete : function()
	   {
	   }
	}); 
}



function doChangeCategory(v, kind, gb){
	getCategory(kind, v, gb);
}


function doCategoryExtraction(v){
	
	var value = 0;

	if(v != "" || v != null){
		
		var arr = v.split('^');
		value = arr[0];

	}

	return value;

}

var tableRowCount = 1;
var maxTableRows = 5;

function addTableRow() {
    if (tableRowCount < maxTableRows) {
        var tableBody = document.getElementById('patent-table-body');

        // 새로운 행(tr) 요소 생성
        var newRow = document.createElement('tr');
        newRow.innerHTML = `
        <td>	
		  <input type="hidden" name="patent_kind[]" value="F">
          <input type="text" name="registration_number[]" id="registration_number${tableRowCount+1}" class="no-MCMX-form__input--02" />
        </td>
        <td>
          <input type="text" name="patent_name[]" id="patent_name${tableRowCount+1}" class="no-MCMX-form__input--02" />
        </td>
        <td>
          <div class="no-filebox">
            <input class="no-file__name no-file__name--patent_file${tableRowCount+1}" value="첨부파일" readonly />
            <label for="patent_file${tableRowCount+1}">파일찾기</label>
            <input type="file" id="patent_file${tableRowCount+1}" name="file-patent[]" />
          </div>
        </td>
      `;

        // 새로운 행을 테이블의 tbody에 추가
        tableBody.appendChild(newRow);

        // 행 개수 증가
        tableRowCount++;
    } else {
        alert('최대 ' + maxTableRows + '개까지만 추가할 수 있습니다.');
    }

	$('#patent_file2').on('change', function () {
        var fileName = $('#patent_file2').val();
        $('.no-file__name--patent_file2').val(fileName);
    });
	$('#patent_file3').on('change', function () {
        var fileName = $('#patent_file3').val();
        $('.no-file__name--patent_file3').val(fileName);
    });
	$('#patent_file4').on('change', function () {
        var fileName = $('#patent_file4').val();
        $('.no-file__name--patent_file4').val(fileName);
    });
	$('#patent_file5').on('change', function () {
        var fileName = $('#patent_file5').val();
        $('.no-file__name--patent_file5').val(fileName);
    });
}

var productCount = 1;
var maxProducts = 5; // 최대 추가 가능한 상품 개수

function addAwardFile() {
    var productList = document.getElementById('patent-file-list');

    if (productCount < maxProducts) {
        // 새로운 li 요소 생성
        var li = document.createElement('li');
        li.innerHTML = `
        <span class="no-MCMX-form__product--num">수상증서${productCount + 1}</span>
        <div class="no-filebox">
          <input class="no-file__name no-file__name--award_file${productCount + 1}" value="파일첨부" placeholder="*파일첨부" readonly />
		  <input type="hidden" name="award_kind[]" value="F">
          <label for="award_file${productCount + 1}">파일찾기</label>
          <input type="file" id="award_file${productCount + 1}" name="award_file[]" />
        </div>
      `;

        // 새로운 li 요소를 리스트에 추가
        productList.appendChild(li);

        // 상품 개수 증가
        productCount++;
    } else {
        alert('최대 ' + maxProducts + '개까지만 추가할 수 있습니다.');
    }

	$('#award_file2').on('change', function () {
        var fileName = $('#award_file2').val();
        $('.no-file__name--award_file2').val(fileName);
    });
	$('#award_file3').on('change', function () {
        var fileName = $('#award_file3').val();
        $('.no-file__name--award_file3').val(fileName);
    });
	$('#award_file4').on('change', function () {
        var fileName = $('#award_file4').val();
        $('.no-file__name--award_file4').val(fileName);
    });
	$('#award_file5').on('change', function () {
        var fileName = $('#award_file5').val();
        $('.no-file__name--award_file5').val(fileName);
    });
}


function doDelete(no, kind) {
    var con = confirm('정말 삭제하시겠습니까?');

    var param = '';
    if (no) {
        param = 'no=' + no + '&kind='+kind+'&mode=delete';
    } else {
        param = 'no=' + $('#no').val() + '&kind='+kind+'&mode=delete';
    }

    if (con) {
        $.ajax({
            type: 'POST',
            url: './ajax/process.php',
            data: param,
            cache: false,
            dataType: 'html',
            success: function (data) {
                var jsonData = $.parseJSON(data);

                if (jsonData.result == 'fail') {
                    alert(jsonData.msg);
                } else if (jsonData.result == 'success') {
                    alert(jsonData.msg);
                    location.href = $("#returnListUrl").val();
                }
            },
            error: function (a, s) {
                alert('처리중 문제가 발생하였습니다.');
                return;
            },
        });
    }
}


function doMatchingDelete(no) {
    var con = confirm('정말 삭제하시겠습니까?');

    var param = '';
    if (no) {
        param = 'no=' + no + '&&mode=matching.delete';
    } else {
        param = 'no=' + $('#no').val() + '&mode=matching.delete';
    }

    if (con) {
        $.ajax({
            type: 'POST',
            url: './ajax/process.php',
            data: param,
            cache: false,
            dataType: 'html',
            success: function (data) {
                var jsonData = $.parseJSON(data);

                if (jsonData.result == 'fail') {
                    alert(jsonData.msg);
                } else if (jsonData.result == 'success') {
                    alert(jsonData.msg);
                    //location.href = $("#returnListUrl").val();
					location.reload();
                }
            },
            error: function (a, s) {
                alert('처리중 문제가 발생하였습니다.');
                return;
            },
        });
    }
}
