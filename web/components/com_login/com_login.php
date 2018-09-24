<?php 
   
   require_once (PATH_BASE.'/framework/abstractcomponent.php');
   require_once (PATH_BASE.'/framework/user.php');

   
   class com_login extends  AbstractComponent {

     
  	 
     public function __construct(CSite $site) {
   	   $this->site=$site;
   	   $this->user=$site->getUser();	
   	 }
   	 
   	 public function getTitle() {
   	 	return "Введите логин и пароль";
   	 }
   	 
   	/**
  	 * Функция возвращает ключевые слова (заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getKeyWords() {
   	 	return "Wifi";	
  	}
  	
   /**
  	 * Функция возвращает description(заголовок для страницы)
  	 * @return  string заголовок страницы
  	 */
  	public function getDescription() { 
   	 	return "Описание страницы";		
  	}
  	 
   	 
   	 public function getCSSFile() {
   	 	return "login.css";
   	 }
   	 
  
     
   	 public function render() {
   	 	switch($this->user->getAuthorizeStatus()) {
   	 		case CUser::AUTHORIZED: $this->renderForm(false); break;
   	 		case CUser::ERROR_LOGIN: $this->renderForm(true);break;
   	 		case CUser::NON_AUTHORIZED: $this->renderForm(false); break;
		};
		
   	 }

	
	 public function renderForm($loginError) {
?>	 	
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

<!-- form method="post" action="/Forms/rpAuth_1" target="_top" onsubmit="LoginClick(document.forms[0].hiddenPassword, document.forms[0].LoginPassword);" -->
<form id="avtoriz" action="login/" method="post" >
<input type="hidden" name="action" value="login" />
<table align="center" background="images/config_bg.gif" border="2" bordercolor="#BFC4CA" cellpadding="0" cellspacing="0" width="540">
  <tbody><tr>
    <td><table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
 
    <tbody><tr><td colspan="3"><table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tbody><tr>
        <td bgcolor="#0C2F83"><img src="images/soz_50_50.png"></td>
</tr>
<tr>
        <td bgcolor="#AA1931" height="4" width="343"><img src="images/dotspacer.gif" align="right" height="1" width="1"></td>
</tr>
</tbody></table>
</td>
</tr>
<tr>
    <td height="31">&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
    <td width="8%">&nbsp;</td>
<td valign="top" width="86%">
      <table align="center" border="0" width="75%">
        <tbody><tr>
          <td align="center"><p class="style1">
          <br>
<br>
</p>
</td>
</tr>
<tr>
          <td align="center">
Управление Wifi    <p>
</p>
Введите логин и пароль     <p>
</p>
</td>
</tr>
</tbody></table>
<table align="center" border="0" width="75%">
<tbody>
<?php if ($loginError) {?>
	<tr align="center">
         <td colspan="2"><span class="error">Ошибочное значение логина\пароля</span></td>
	</tr>
	
<?php } ?>

	<tr>
		<td align="right" width="44%"><strong>Логин :          </strong> </td>
		<td><input size="30" maxlength="30" id="login" name="login"/></td>
	</tr>
	<tr>
        <td align="right" width="44%"><strong><img src="images/i_key.gif" align="absmiddle" height="17" width="11"> Пароль :          </strong> </td>
		<td><input size="30" maxlength="30" type="password" id="password" name="password"/><input name="hiddenPassword" value="" type="hidden"></td>
	</tr>

	<tr align="center">
         <td colspan="2"><span class="max">( 4-30 знаков, без пробелов )          </span></td>
	</tr>
</tbody>
</table>
<br>

<table align="center" border="0" width="60%">
        <tbody><tr>
          <td>&nbsp;</td>
</tr>
<tr>
          <td align="center">
<input name="submit_button" value="Войти" type="submit">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</td>
</tr>
<tr>
          <td align="center" height="35">&nbsp;</td>
</tr>
</tbody></table>
</td>
<td width="6%">&nbsp;</td>
</tr>
</tbody></table>
</td>
</tr>
</tbody></table>

</form>



 	 	
<?php 	 	
   	 }
   	
   	 public function getAuxScripts() {
   	  return array();
   	 }
   }

?>
