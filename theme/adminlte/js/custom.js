
//checkbox all
function check_all(f)
{
    var chk = document.getElementsByName("chk[]");

    for (i=0; i<chk.length; i++)
        chk[i].checked = f.chkall.checked;
}



// 상태값 
function get_status_select($param_status)
{
    
}

// $('#cond_dropdown .dropdown-menu li > a').bind('click', function (e) {
//     var html = $(this).html();
//     $('#cond_dropdown button.dropdown-toggle').html(html + ' <span class="caret"></span>');
// });


function ajaxNetVoid(type , url , param){
	console.log(type , url , param);
	$.ajax({
		type : type,
		url : url,
		data : param,
		success : function(data , textStatus , xhr ) {
			alert(data);
		},	
		error : function(xhr, status, error) {
			alert("API 에러발생");
		}
	});
}

function ajaxNetInt(type , url , param){
	
    if(type == "") {
        type = "POST";
    }

	$.ajax({
		type : type,
		url : url,
		data : param,

		success : function(data , textStatus , xhr ) {
			
		},	
		
		error : function(xhr, status, error) {
			
		}
	});
}


function telHyphen(target) {
	target.value = target.value.replace(/[^0-9]/g, '').replace(/^(\d{2,3})(\d{3,4})(\d{4})$/, `$1-$2-$3`);
}


function addCommas(input) {
    // 입력된 값에서 콤마 제거
    var value = input.value.replace(/,/g, '');

	// 입력된 값이 숫자가 아닌 경우 취소
    if (!/^[0-9]+$/.test(value)) {
        input.value = '';
        return;
    }
  
	value = value.replace(/^0+/, ''); // 숫자 시작 부분의 0 제거
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  
    // 콤마가 추가된 값으로 다시 입력 필드에 설정
    input.value = value;
}