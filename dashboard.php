<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Database connection
$db = new SQLite3("db.sqlite");

// Function to render tables
function renderTable($title, $rows, $headers, $deleteScript, $type) {
    echo "<div class='section'>";
    echo "<h3>$title</h3>";
    if (count($rows) > 0) {
        echo "<table><tr>";
        foreach ($headers as $header) {
            echo "<th>$header</th>";
        }
        echo "<th>إجراء</th></tr>";

        foreach ($rows as $row) {
            echo "<tr>";
            foreach ($headers as $key) {
                echo "<td>" . htmlspecialchars($row[$key]) . "</td>";
            }
            echo "<td><form method='POST' action='$deleteScript' style='display:inline;'>
                    <input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>
                    <input type='hidden' name='type' value='" . htmlspecialchars($type) . "'>
                    <button type='submit'>🗑 حذف</button>
                  </form></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>لا توجد بيانات لعرضها.</p>";
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الجدار الناري</title>
    <style>
        body { font-family: Arial; direction: rtl; text-align: center; background-color: #f8f8f8; margin: 0; padding: 0; }
        .section { background: #fff; padding: 20px; margin: 20px auto; width: 90%; max-width: 900px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: right; }
        th { background-color: #f2f2f2; }
        input, button, select { padding: 10px; margin: 5px; border-radius: 5px; border: 1px solid #ccc; font-size: 1em; }
        button { background-color: #4CAF50; color: white; cursor: pointer; border: none; }
        button:hover { opacity: 0.9; }
        h2 { color: #333; margin-top: 30px; }
        h3 { color: #555; margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        #ruleResult { margin-top: 10px; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>

    <h2>👨‍💻 لوحة التحكم - الجدار الناري الذكي</h2>

    <div class="section">
        <h3>🔐 تحديث معلومات الدخول</h3>
        <form method="POST" action="update_credentials.php">
            <input name="username" placeholder="اسم المستخدم الجديد" required>
            <input type="password" name="password" placeholder="كلمة المرور الجديدة" required>
            <input type="password" name="confirm_password" placeholder="تأكيد كلمة المرور" required>
            <button type="submit">تحديث</button>
        </form>
    </div>

    <div class="section">
        <h3>📊 إحصائيات الحظر</h3>
        <p>📍 IP محظور: <span id="ipCount">0</span></p>
        <p>🎯 المنافذ المحظورة: <span id="portCount">0</span></p>
        <p>📡 البروتوكولات المحظورة: <span id="protoCount">0</span></p>
        <p>📜 قواعد مخصصة: <span id="customCount">0</span></p>
        <canvas id="banChart" width="400" height="200"></canvas>
    </div>

    <div class="section">
        <h3>🛑 حظر عنوان IP</h3>
        <form id="blockForm">
            <input type="text" name="ip" placeholder="أدخل IP لحظره" required>
            <button type="submit">حظر</button>
        </form>
        <div id="rulesTable">جاري تحميل قائمة الحظر...</div>
    </div>

    <div class="section">
        <h3>🚫 حظر منفذ</h3>
        <form id="blockPortForm">
            <input type="number" name="port" placeholder="أدخل رقم المنفذ" required>
            <select name="direction" required>
                <option value="in">🔽 وارد (IN)</option>
                <option value="out">🔼 صادر (OUT)</option>
            </select>
            <button type="submit">حظر</button>
        </form>
        <div id="portsTable">جاري تحميل المنافذ المحظورة...</div>
    </div>

    <div class="section">
        <h3>📡 حظر بروتوكول</h3>
        <form id="protoForm">
            <select name="protocol" required>
                <option value="tcp">TCP</option>
                <option value="udp">UDP</option>
                <option value="icmp">ICMP</option>
            </select>
            <select name="direction" required>
                <option value="in">🔽 وارد (IN)</option>
                <option value="out">🔼 صادر (OUT)</option>
            </select>
            <button type="submit">حظر</button>
        </form>
        <div id="protoTable">جاري تحميل البروتوكولات المحظورة...</div>
    </div>

    <div class="section">
        <h3>📋 إضافة قاعدة مخصصة</h3>
        <form id="customRuleForm">
            <input type="text" name="ip" placeholder="IP (اختياري)">
            <input type="text" name="port" placeholder="Port (اختياري)">
            <select name="protocol">
                <option value="">أي بروتوكول</option>
                <option value="tcp">TCP</option>
                <option value="udp">UDP</option>
                <option value="icmp">ICMP</option>
            </select>
            <select name="direction">
                <option value="in">🔽 وارد (IN)</option>
                <option value="out">🔼 صادر (OUT)</option>
            </select>
            <button type="submit">➕ إضافة القاعدة</button>
        </form>
        <div id="ruleResult"></div>
    </div>

    <?php
    // Fetch data for tables using the database connection established earlier
    renderTable("📜 القواعد المخصصة", $db->query("SELECT id, ip, port, protocol, direction, time FROM custom_rules")->fetchArray(SQLITE3_ASSOC), ["ip", "port", "protocol", "direction", "time"], "delete_rule.php", "custom");
    renderTable("📍 عناوين IP المحظورة", $db->query("SELECT id, ip, time FROM firewall_events")->fetchArray(SQLITE3_ASSOC), ["ip", "time"], "delete_rule.php", "ip");
    renderTable("🎯 المنافذ المحظورة", $db->query("SELECT id, port, direction, time FROM blocked_ports")->fetchArray(SQLITE3_ASSOC), ["port", "direction", "time"], "delete_rule.php", "port");
    renderTable("📡 البروتوكولات المحظورة", $db->query("SELECT id, protocol, direction, time FROM blocked_protocols")->fetchArray(SQLITE3_ASSOC), ["protocol", "direction", "time"], "delete_rule.php", "protocol");
    $db->close();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.onload = function() {
            loadStats(); loadRules(); loadPorts(); loadProtocols();
        };

        document.getElementById("blockForm").onsubmit = async function(e) {
            e.preventDefault();
            const form = new FormData(this);
            await fetch("block_ip.php", { method: "POST", body: form });
            loadRules(); loadStats();
        };

        document.getElementById("blockPortForm").onsubmit = async function(e) {
            e.preventDefault();
            const form = new FormData(this);
            await fetch("block_port.php", { method: "POST", body: form });
            loadPorts(); loadStats();
        };

        document.getElementById("protoForm").onsubmit = async function(e) {
            e.preventDefault();
            const form = new FormData(this);
            await fetch("block_protocol.php", { method: "POST", body: form });
            loadProtocols(); loadStats();
        };

        document.getElementById("customRuleForm").onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            try {
                const res = await fetch("add_rule.php", { method: "POST", body: formData });
                const text = await res.text();
                document.getElementById("ruleResult").innerText = text;
                loadRules(); loadStats();
            } catch (error) {
                document.getElementById("ruleResult").innerText = "Error: " + error.message;
            }
        };

        async function loadRules() {
            try {
                const res = await fetch("list_rules.php");
                const html = await res.text();
                document.getElementById("rulesTable").innerHTML = html;
            } catch (e) { console.error(e); }
        }

        async function loadPorts() {
            try {
                const res = await fetch("list_blocked_ports.php");
                const html = await res.text();
                document.getElementById("portsTable").innerHTML = html;
            } catch (e) { console.error(e); }
        }

        async function loadProtocols() {
            try {
                const res = await fetch("list_blocked_protocols.php");
                const html = await res.text();
                document.getElementById("protoTable").innerHTML = html;
            } catch (e) { console.error(e); }
        }

        let banChartInstance = null;
        async function loadStats() {
            try {
                const res = await fetch("get_stats.php");
                const data = await res.json();
                document.getElementById("ipCount").innerText = data.ip || '0';
                document.getElementById("portCount").innerText = data.port || '0';
                document.getElementById("protoCount").innerText = data.protocol || '0';
                document.getElementById("customCount").innerText = data.custom || '0';
                drawChart(data);
            } catch (e) {
                document.getElementById("ipCount").innerText = 'Error';
                document.getElementById("portCount").innerText = 'Error';
                document.getElementById("protoCount").innerText = 'Error';
                document.getElementById("customCount").innerText = 'Error';
            }
        }

        function drawChart(data) {
            const ctx = document.getElementById('banChart').getContext('2d');
            if (banChartInstance) banChartInstance.destroy();
            banChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['IP', 'Port', 'Protocol', 'Custom Rules'],
                    datasets: [{
                        data: [data.ip, data.port, data.protocol, data.custom],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#8AFFC1'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'إحصائيات الحظر حسب النوع' }
                    }
                }
            });
        }
    </script>
</body>
</html>
