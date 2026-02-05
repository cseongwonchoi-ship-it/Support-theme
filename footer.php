</div>
    <!-- 메인 컨텐츠 종료 -->

</div>
<!-- main-wrapper 종료 -->

<!-- 이탈 방지 팝업 -->
<div class="exit-popup-overlay" id="exitPopup">
    <div class="exit-popup">
        <div class="exit-popup-title">🎁 잠깐! 놓치신 혜택이 있어요</div>
        <div class="exit-popup-desc">
            지금 확인 안 하면<br/>
            <strong>최대 300만원</strong> 지원금을 못 받을 수 있어요!
        </div>
        <button class="exit-popup-btn" onclick="closePopupAndScroll()">
            내 지원금 확인하기 →
        </button>
        <button class="exit-popup-close" onclick="closePopupNotNow()">
            다음에 할게요
        </button>
    </div>
</div>

<!-- 푸터 -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <div class="footer-brand"><?php bloginfo('name'); ?></div>
            <ul class="footer-info">
                <li>
                    <i>📍</i>
                    사업자 주소: <?php echo esc_html(get_option('support_theme_business_address', '')); ?>
                </li>
                <li>
                    <i>🏢</i>
                    사업자 번호: <?php echo esc_html(get_option('support_theme_business_number', '123-45-67890')); ?>
                </li>
            </ul>
        </div>
        <!-- 아래 내용 변경은 명백한 저작권법 위법이며, 민형사 소송에 있어 철저한 불관용 원칙을 적용합니다 -->
        <div class="footer-right">
            <p>제작자 : 아로스</p>
            <p>홈페이지 : <a href="https://aros100.com" target="_blank" rel="noopener noreferrer">바로가기</a></p>
            <p class="footer-copyright">Copyrights &copy; <?php echo date('Y'); ?> All Rights Reserved by (주)아백</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
