<?php
// ไฟล์: quick_win_chart.php
// ใช้สำหรับแสดงผลกราฟ Quick Win

// ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
if (!isset($dashboard_data) || empty($dashboard_data)) {
    echo "<div class='alert alert-warning text-center'>ไม่มีข้อมูลสำหรับแสดงผลกราฟ Quick Win</div>";
    return; // หยุดการทำงานถ้าไม่มีข้อมูล
}

// เตรียมข้อมูลสำหรับ Chart.js
$labels = json_encode(array_column($dashboard_data, 'question_text_with_number'));

$scores = array_map(function($score) {
    return $score ?? 0;
}, array_column($dashboard_data, 'average_score'));
$values = json_encode($scores);

$chart_title = "สรุปผลความสำเร็จตามเป้าหมาย (Quick Win)";

?>

<div class="card chart-card">
    <div class="card-header card-header-custom">
        <h5 class="card-title mb-0"><i class="fas fa-trophy"></i> <?php echo $chart_title; ?></h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <canvas id="quickWinChart"></canvas>
            </div>
            <div class="col-md-4">
                <div id="quickWinLegend" class="custom-legend mt-4"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('quickWinChart').getContext('2d');
    const labels = <?php echo $labels; ?>;
    const dataValues = <?php echo $values; ?>;
    const bgColors = <?php echo $js_background_colors; ?>;

    const quickWinChart = new Chart(ctx, {
        type: 'bar', // เปลี่ยนเป็นกราฟแท่ง
        data: {
            labels: labels,
            datasets: [{
                label: 'คะแนนเฉลี่ย',
                data: dataValues,
                backgroundColor: bgColors,
                borderColor: bgColors.map(color => color.replace('0.7', '1')),
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // ทำให้เป็นกราฟแท่งแนวนอน
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    max: 5 // กำหนดค่าสูงสุดของแกน X
                }
            },
            plugins: {
                legend: {
                    display: false // ปิดการแสดงผล legend ของ Chart.js
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (value, context) => {
                        return parseFloat(value).toFixed(2); // แสดงทศนิยม 2 ตำแหน่ง
                    },
                    color: '#333',
                    font: {
                        weight: 'bold'
                    }
                }
            }
        }
    });

    // สร้าง Legend เอง
    const legendContainer = document.getElementById('quickWinLegend');
    labels.forEach((label, index) => {
        legendContainer.innerHTML += `<div class="legend-item"><div class="legend-color-box" style="background-color: ${bgColors[index % bgColors.length]}"></div><span>${label}</span></div>`;
    });
});
</script>