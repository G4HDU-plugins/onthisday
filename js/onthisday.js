//otd-brief

//otd-day

//otd-month

$( document ).ready(function() {
    $('#otddataform').submit(function(){
        var input1 = $('#otd-brief').val().length;
        var input2 = $('#otd-day').val().length;
        var input3 = $('#otd-month').val().length;
        

        if(input1==0){
            $('#otd-brief').addClass('otdError');
        }else{
            $('#otd-brief').removeClass('otdError');
        }
        if(input2==0){
            $('#otd-day').addClass('otdError');
        }else{
            $('#otd-day').removeClass('otdError');
        }
        if(input3==0){
            $('#otd-month').addClass('otdError');
        }else{
            $('#otd-month').removeClass('otdError');
        }
        if(input1 == 0 || input2 == 0 || input3 == 0){
           // alert ("Missing field");
            return false;
        }
    });
});