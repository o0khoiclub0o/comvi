		<h1>Login</h1>
		<form method="post" action="">
<?php if (!empty($this->vars['content']->errors)) {?>
			<ul class="errors">
<?php foreach ($this->vars['content']->errors as $error) {?>
				<li><?=$error?></li>
<?php }?>
			</ul>
<?php }?>
			<label>Email</label>
			<input type="text" name="email" maxlength="50" />

			<label>Password</label>
			<input type="password" name="password" maxlength="32" />

			<input type="submit" name="submit" value="Login" class="mrT10" />
		</form>
