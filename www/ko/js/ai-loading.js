/**
 * AI 분석 로딩 오버레이 공통 모듈
 *
 * 사용법:
 *   <link rel="stylesheet" href="css/ai-loading.css">
 *   <!-- ai-loading HTML 포함 -->
 *   <script src="js/ai-loading.js"></script>
 *
 *   showAiLoading('company_list.html?cat=all&type=ai');
 */
function showAiLoading(redirectUrl) {
    var overlay = document.getElementById('aiLoadingOverlay');
    if (!overlay) return;
    var steps = overlay.querySelectorAll('.ai-loading_step');
    var bar = document.getElementById('aiLoadingBarFill');
    overlay.classList.add('is_open');
    bar.style.width = '0%';
    steps.forEach(function(s) { s.classList.remove('done'); });

    var delays = [600, 1200, 1800, 2400, 3000, 3600];
    delays.forEach(function(d, i) {
        setTimeout(function() {
            steps[i].classList.add('done');
            steps[i].querySelector('span:last-child').textContent =
                steps[i].querySelector('span:last-child').textContent.replace('중...', '완료 ✓');
            bar.style.width = ((i + 1) / steps.length * 100) + '%';
        }, d);
    });

    setTimeout(function() {
        window.location.href = redirectUrl;
    }, 4200);
}