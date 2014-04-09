<h1 class="title">Login or Create an Account</h1>
<div class="os-login">
<?php if ($_SERVER['QUERY_STRING'] == 'q=user/login/colorbox&destination=civicrm/event/register?id=69&reset=1') { ?>
<p>If you registered in 2010, 2011, or 2012 for SF Open Studios, you already have an ArtSpan account.  <a href="/user/password?destination=sf-open-studios/registration">Click here</a> if you forgot your password. You do not need to create a new account.</p>
<?php print drupal_render($form); ?>
<p>New SF Open Studios artists and artists who did not register last year need to create a new account.  <a href="/user/register">Click here</a> to create a new account.</p>
<?php } else {?>
<p><a href="/user/password">Click here</a> if you forgot your password.</p>
<?php print drupal_render($form); ?>
<p>New SF Open Studios artists and artists who did not register last year need to create a new account.  <a href="/user/register">Click here</a> to create a new account.</p>
<?php } ?>
</div>
