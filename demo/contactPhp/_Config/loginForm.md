Login for Developer's Zone
==========================

<form name="loginForm" method="post" id="loginForm" action="">
<table>
<tr><th><label>login_id:</th><td><input type="text" name="auth_name" /></label></td></tr>
<tr><th><label>password:</th><td><input type="password" name="auth_pass" /></label><br /></td></tr>
<tr><td colspan="2">
<input type="hidden" name="auth_act" value="authNot" />
<input type="submit" name="submit" value="Login" />
</td></tr>
</table>
</form>

    *   AuthNot module requires to have same login_id and password.  
        example: id=admin, pw=admin