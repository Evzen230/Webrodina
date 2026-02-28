<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Centrální Registr</title>
    <style>
        /* ANIMACE POZADÍ */
        @keyframes bgShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ANIMACE PULZOVÁNÍ BOXU */
        @keyframes boxPulse {
            0% { box-shadow: 0 0 20px rgba(0, 86, 179, 0.4), 0 0 5px rgba(0, 86, 179, 0.1) inset; border-color: #004494; }
            100% { box-shadow: 0 0 40px rgba(0, 110, 255, 0.6), 0 0 15px rgba(0, 86, 179, 0.3) inset; border-color: #0066cc; }
        }

        body {
            background: radial-gradient(circle at center, #1a2a3a 0%, #0d0d0d 80%);
            background-size: 150% 150%;
            animation: bgShift 15s ease infinite; 
            color: #fff;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden; 
        }

        .login-box {
            background: rgba(26, 26, 26, 0.8); 
            backdrop-filter: blur(10px); 
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            width: 320px;
            border: 1px solid #004494;
            animation: boxPulse 3s ease-in-out infinite alternate;
        }

        h2 {
            color: #4da6ff; 
            text-transform: uppercase;
            margin-bottom: 25px;
            letter-spacing: 2px;
            font-weight: 600;
            text-shadow: 0 0 10px rgba(77, 166, 255, 0.3);
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: rgba(34, 34, 34, 0.9);
            border: 1px solid #444;
            color: #fff;
            border-radius: 6px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            border-color: #4da6ff;
            box-shadow: 0 0 8px rgba(77, 166, 255, 0.4);
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(to right, #0056b3, #004494);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 15px;
            transition: 0.3s;
            font-size: 14px;
            letter-spacing: 1px;
        }

        button:hover {
            background: linear-gradient(to right, #0066cc, #0056b3);
            box-shadow: 0 0 15px rgba(0, 110, 255, 0.4);
            transform: translateY(-2px);
        }
        
        button:active {
             transform: translateY(0px);
        }

        .error {
            color: #ff4444;
            margin-top: 15px;
            font-size: 14px;
            background: rgba(255, 68, 68, 0.1);
            padding: 10px;
            border-radius: 4px;
            display: none;
        }

        .footer {
            margin-top: 25px;
            font-size: 12px;
            color: #777;
        }
        
        .register-link {
            display: inline-block;
            margin-top: 15px;
            color: #4da6ff;
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }
        
        .register-link:hover { 
            color: #fff; 
            text-decoration: underline; 
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Centrální Registr</h2>
        <form action="" method="post">
            <input type="text" name="username" placeholder="Služební číslo / Login" required autofocus autocomplete="off">
            <input type="password" name="password" placeholder="Heslo" required autocomplete="off">
            <button type="submit">PŘIHLÁSIT SE</button> 
        </form>
        
        <div class="error"></div>
        
        <a href="registrace_obcana.php" class="register-link">📝 Žádost o vydání průkazu totožnosti</a>
        
        <div class="footer">Vytvořil: <strong>Sunkys</strong></div>
    </div>

</body>
</html>