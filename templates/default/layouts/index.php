<?php if ($this->vars['user']->id === 0) {?>
		<h1>Hello Guess!</h1>
		<ul>
			<li>You need to <a href="<?=$this->vars['document']->baseurl?>user?task=login">Login</a> to be able to edit your profile.</li>
			<li>If you haven't had any account, you can <a href="<?=$this->vars['document']->baseurl?>user?task=register">Click Here</a> to register a new account.</li>
		</ul>
<?php } else {?>
		<h1>Hello <?=$this->vars['user']->fullname?>,</h1>
		<ul>
			<li><a href="<?=$this->vars['document']->baseurl?>user?task=edit">Profile</a></li>
			<li><a href="<?=$this->vars['document']->baseurl?>user?task=logout">Logout</a></li>
		</ul>
<?php }?>
