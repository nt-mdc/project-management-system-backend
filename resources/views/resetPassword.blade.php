<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f7f7f7;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .container {
        width: 100%;
        max-width: 400px;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .reset-form h2 {
        margin-bottom: 20px;
        color: #333;
        text-align: center;
    }

    .input-group {
        margin-bottom: 15px;
    }

    .input-group label {
        display: block;
        margin-bottom: 5px;
        color: #555;
    }

    .input-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 16px;
    }

    button {
        width: 100%;
        padding: 10px;
        background-color: #4681f4;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
    }

    button:hover {
        background-color: #5783db;
    }

</style>
<body>
<div class="container">
    <div class="reset-form">
        <h2>Redefinir Senha</h2>
        <form method="POST">
            @csrf
            <div class="input-group">
                <label for="password">Nova Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="password_confirmation">Confirme a Senha:</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
            <div>
                @if ($errors->any())
                    <ul style="color: #cb0000;font-size: 15px;">
                        @foreach ($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <input type="hidden" name="id" value="{{$user->id}}">
            <button type="submit">Redefinir Senha</button>
        </form>
    </div>
</div>
</body>