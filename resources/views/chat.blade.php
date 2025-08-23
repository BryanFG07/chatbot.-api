<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ChatBot</title>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
</head>
<body>
    <h1>ChatBot</h1>


    <!-- Question form -->
    <div class="chat-input">
        <input type="text" id="question" placeholder="Type your question..." maxlength="1000">
        <button id="askBtn">Ask</button>
    </div>

    <!-- Answer section -->
    <div id="response"></div>


    <h2 class="history-title">History</h2>
    <div class="history-controls">
        <label for="historyLimit" class="history-label">Max responses:</label>
        <input type="number" id="historyLimit" min="1" max="100" value="10" class="history-input">
        <button id="updateHistoryBtn">Update</button>
    </div>
    <div id="history"></div>

    <script src="{{ asset('js/chat.js') }}"></script>
</body>
</html>
