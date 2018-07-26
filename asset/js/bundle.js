//document.getElementsByTagName("h1")[0].innerHTML+='!';
$.ajax({
    url:"hello/cool/90",
    method:"get",
    dataType:"json",
    success:function(res){
        console.log('ajax',res);
    },
    error:function(){
        console.warn('ajax error');
    }
})