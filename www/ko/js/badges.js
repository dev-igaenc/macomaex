/**
 * 기업카드 뱃지 공통 렌더링 모듈
 *
 * 사용법:
 *   <link rel="stylesheet" href="css/badges.css">
 *   <script src="js/badges.js"></script>
 *
 *   var html = companyBadgesHtml(companyObj);
 *   // companyObj: { badge_growth: 15, badge_stability: 5, badge_venture: 'Y' }
 */
(function () {
    function esc(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function companyBadgesHtml(c) {
        var parts = [];
        if (c.badge_growth != null && c.badge_growth !== '') {
            parts.push('<span class="mCompanyCard_badge mCompanyCard_badge_top_growth">\uC131\uC7A5TOP' + esc(String(c.badge_growth)) + '%</span>');
        }
        if (c.badge_stability != null && c.badge_stability !== '') {
            parts.push('<span class="mCompanyCard_badge mCompanyCard_badge_top_stability">\uC7AC\uBB34\uC548\uC815TOP' + esc(String(c.badge_stability)) + '%</span>');
        }
        if (c.badge_venture === 'Y') {
            parts.push('<span class="mCompanyCard_badge mCompanyCard_badge_venture">\uBCA4\uCC98\uC778\uC99D</span>');
        }
        if (parts.length === 0) {
            return '<div class="mCompanyCard_badges"><span class="mCompanyCard_badge mCompanyCard_badge_placeholder">\uBC43\uC9C0 \uC900\uBE44\uC911</span></div>';
        }
        return '<div class="mCompanyCard_badges">' + parts.join('') + '</div>';
    }

    window.companyBadgesHtml = companyBadgesHtml;
})();