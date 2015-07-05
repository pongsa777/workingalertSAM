<html>
<head>
    <meta charset="utf-8">
    <title>login</title>    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="asset/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="asset/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="asset/mainset.css">
    <script src="asset/bootstrap/js/bootstrap.min.js"></script>
    
</head>
<body>
    <div class="container">
      <form class="form-signin" action="authen.php" method="post">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button><br>
          <a href="createuser.html">
              <button class="btn btn-lg btn-warning btn-block" type="button">Create New user</button>
          </a>
      </form>

    </div> <!-- /container -->
    <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>