
document.addEventListener("DOMContentLoaded", () => {
    const askBtn = document.getElementById("askBtn");
    const questionInput = document.getElementById("question");
    const responseDiv = document.getElementById("response");
    const historyDiv = document.getElementById("history");
    const historyLimitInput = document.getElementById("historyLimit");
    const updateHistoryBtn = document.getElementById("updateHistoryBtn");

    let currentLimit = parseInt(historyLimitInput.value, 10) || 10;

    async function loadHistory(limit = currentLimit) {
        try {
            const res = await fetch(`/api/history?limit=${limit}`);
            const json = await res.json();
            const history = json.data || [];

            if (history.length === 0) {
                historyDiv.innerHTML = "<p>No history yet.</p>";
            } else {
                historyDiv.innerHTML = history.map(item =>
                    `<div class="chat-box">
                        <div class="question">Q: ${item.question}</div>
                        <div class="answer">A: ${item.answer}</div>
                        <small>${item.created_at}</small>
                    </div>`
                ).join("");
            }
        } catch (error) {
            historyDiv.innerHTML = "<p>Error loading history</p>";
            //console.error(error);
        }
    }

    askBtn.addEventListener("click", async () => {
        const question = questionInput.value.trim();
        if (!question) return;

        responseDiv.textContent = "Loading...";

        try {
            const res = await fetch("/api/ask", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ question })
            });
            const data = await res.json();
            if (data.success === false) {
                responseDiv.textContent = data.message || data.error || "Error";
            } else {
                responseDiv.textContent = data.answer || "No answer";
            }
            questionInput.value = "";
            loadHistory(currentLimit);
        } catch (error) {
            responseDiv.textContent = "Request failed.";
        }
    });

    updateHistoryBtn.addEventListener("click", () => {
        let newLimit = parseInt(historyLimitInput.value, 10);
        if (isNaN(newLimit) || newLimit < 1 || newLimit > 100) {
            historyLimitInput.value = currentLimit;
            return;
        }
        currentLimit = newLimit;
        loadHistory(currentLimit);
    });

    loadHistory(currentLimit);
});
