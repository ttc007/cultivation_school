<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta name="format-detection" content="telephone=no"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="MobileOptimized" content="176"/>
    <meta name="HandheldFriendly" content="True"/>
    <meta name="robots" content="noindex,nofollow"/>
    <title></title>
    <script src="https://telegram.org/js/telegram-web-app.js?1"></script>
    <script>
        function setThemeClass() {
            document.documentElement.className = Telegram.WebApp.colorScheme;
        }

        Telegram.WebApp.onEvent('themeChanged', setThemeClass);
        setThemeClass();
    </script>
</head>
<body>
<pre><code id="user_id"></code></pre>
<script type="application/javascript">
	/*
     * This is a demo code for Telegram WebApp for Bots
     * It contains basic examples of how to use the API
     * Note: all requests to backend are disabled in this demo, you should use your own backend
     */

    const DemoApp = {
        initData      : Telegram.WebApp.initData || '',
        initDataUnsafe: Telegram.WebApp.initDataUnsafe || {},
        MainButton    : Telegram.WebApp.MainButton,

        init(options) {
            document.body.style.visibility = '';
            Telegram.WebApp.ready();
            Telegram.WebApp.MainButton.setParams({
                text      : 'CLOSE WEBVIEW',
                is_visible: true
            }).onClick(DemoApp.close);
        },
        expand() {
            Telegram.WebApp.expand();
        },
        close() {
            Telegram.WebApp.close();
        },
    }
</script>
<script type="text/javascript">
	var user = DemoApp.initDataUnsafe.user;
	document.getElementById('user_id').innerHTML = user.first_name + " " + user.last_name;
</script>
</body>
</html>