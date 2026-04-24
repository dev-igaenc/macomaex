// company_list_ai.php 전용 최소 인터랙션
(function () {
    function initTypeSwitch() {
        var wrap = document.querySelector('.list-type-switch');
        if (!wrap) return;

        var slider = wrap.querySelector('.list-type-slider');
        var btns = wrap.querySelectorAll('.list-type-btn');
        var panels = document.querySelectorAll('.ai-list-panel');
        var countEl = document.getElementById('aiCompanyCount');

        function updateSlider() {
            var active = wrap.querySelector('.list-type-btn.on');
            if (!slider || !active) return;
            slider.style.width = active.offsetWidth + 'px';
            slider.style.left = active.offsetLeft + 'px';
        }

        function setType(type) {
            btns.forEach(function (btn) {
                var on = btn.getAttribute('data-type') === type;
                btn.classList.toggle('on', on);
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                var on = panel.getAttribute('data-type') === type;
                panel.classList.toggle('is-active', on);
                if (on && countEl) {
                    countEl.textContent = panel.getAttribute('data-total') || '0';
                }
            });

            updateSlider();
        }

        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setType(btn.getAttribute('data-type') || 'buyer');
            });
        });

        var initial = 'buyer';
        var active = wrap.querySelector('.list-type-btn.on');
        if (active) initial = active.getAttribute('data-type') || 'buyer';
        setType(initial);
        window.addEventListener('resize', updateSlider);
    }

    document.addEventListener('DOMContentLoaded', initTypeSwitch);
})();

