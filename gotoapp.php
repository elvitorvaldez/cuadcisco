<?php

    $url = $this->url("home");
    $url_gc = \str_replace("auth", "gestion_cuad", $url);
    $url_rep = \str_replace("auth", "reportes", $url);
    $url_dir = \str_replace("auth", "directorio", $url);
    $user=$auth["userId"];
    $psw=$auth["psw"];
    $role=$auth["user_group"];
    $apps=$lista;
    $fullName=$auth["firstName"]." ".$auth["lastName"];
    $app=strtolower($_GET['app']);
?>

<!--form para el inicio de sesion con app desde cookie-->
 <form name="toCookie" id="toCookie" method="post" target="frameapp" action="https://ucx-<?php echo $app;?>.vsys.com/clarusipc/j_clarus_security_check">
                <input type="hidden" name="j_username" id="j_username" value="<?php echo trim($user);?>">
                <input type="hidden" name="j_password" id="j_password" value="<?php echo trim($psw);?>">
  </form>


<script languaje="javascript">
 $( document ).ready(function() {
      var appCookie="";
      validateCookie(); 
      muestraApp();
});
 


    function muestraApp()
    {
        alert("haz submit mana");
    $('#toCookie').submit(); 
    }
    
function validateCookie(){
    
var cookies=document.cookie; 
var cookieVal=cookies.split("=");
appCookie=cookieVal[1];  

if (appCookie.length>20)
{
    var usr = document.getElementById('j_username').value;
    var psw = document.getElementById('j_password').value;
     document.getElementById('datagridApps').style.display = 'none'; 
     document.getElementById('jumbotronApps').style.display = 'none'; 
     var form=document.getElementById('toCookie');
     form.action=appCookie;
     var frameapp=document.getElementById('frameapp');
     frameapp.style.display = 'block'; 
     form.submit();
     //frameapp.src= appCookie; 
}  
}     
    
    
</script>