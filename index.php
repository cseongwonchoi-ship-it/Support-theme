<?php
/**
 * 메인 템플릿 파일
 * 
 * @package Support_Theme
 */

get_header(); ?>

<!-- 상단 인트로 -->
<div class="intro-section">
    <span class="intro-badge">신청마감 D-3일</span>
    <p class="intro-sub">숨은 보험금 1분만에 찾기!</p>
    <h2 class="intro-title">숨은 지원금 찾기</h2>
</div>

<!-- 애드센스 광고 위치 -->
<?php
$ad_code = support_theme_get_ad_code();
if (!empty($ad_code)):
?>
<div>
    <?php echo $ad_code; ?>
</div>
<?php endif; ?>

<!-- 정보 박스 -->
<div class="info-box">
    <div class="info-box-header">
        <span class="info-box-icon">🏷️</span>
        <span class="info-box-title">신청 안하면 절대 못 받아요</span>
    </div>
    <div class="info-box-amount">1인 평균 127만원 환급</div>
    <p class="info-box-desc">대한민국 92%가 놓치고 있는 정부 지원금! 지금 확인하고 혜택 놓치지 마세요.</p>
</div>

<!-- 지원금 카드 그리드 -->
<div class="info-card-grid">
    <?php
    $cards = support_theme_get_cards();
    $connect_url = support_theme_get_connect_url();
    $ad_inserted = array(); // 광고 삽입 위치 추적
    
    if (!empty($cards)):
        foreach ($cards as $index => $card):
            // 광고 삽입 위치: 0번째, 3번째, 6번째 카드 전에
            if (!empty($ad_code) && in_array($index, array(0, 3, 6)) && !in_array($index, $ad_inserted)):
                $ad_inserted[] = $index;
    ?>
    <!-- 광고 카드 -->
    <div class="ad-card">
        <div style="display:flex; justify-content:center; width:100%;">
            <?php echo $ad_code; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 지원금 카드 -->
    <a class="info-card<?php echo $index === 0 ? ' featured' : ''; ?>" href="<?php echo esc_url($connect_url); ?>">
        <div class="info-card-highlight">
            <?php if ($index === 0): ?>
            <span class="info-card-badge">🔥 인기</span>
            <?php endif; ?>
            <div class="info-card-amount"><?php echo esc_html($card['amount']); ?></div>
            <div class="info-card-amount-sub"><?php echo esc_html($card['amountSub']); ?></div>
        </div>
        <div class="info-card-content">
            <h3 class="info-card-title"><?php echo esc_html($card['keyword']); ?></h3>
            <p class="info-card-desc"><?php echo esc_html($card['description']); ?></p>
            <div class="info-card-details">
                <div class="info-card-row">
                    <span class="info-card-label">지원대상</span>
                    <span class="info-card-value"><?php echo esc_html($card['target']); ?></span>
                </div>
                <div class="info-card-row">
                    <span class="info-card-label">신청시기</span>
                    <span class="info-card-value"><?php echo esc_html($card['period']); ?></span>
                </div>
            </div>
            <div class="info-card-btn">
                지금 바로 신청하기 <span class="btn-arrow">→</span>
            </div>
        </div>
    </a>
    <?php 
        endforeach;
    else:
    ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px;">
        <p style="font-size: 18px; color: #666;">등록된 지원금 카드가 없습니다.</p>
        <p style="font-size: 14px; color: #999; margin-top: 10px;">
            WordPress 관리자 &gt; 지원금 설정에서 카드를 추가해주세요.
        </p>
    </div>
    <?php endif; ?>
</div>

<!-- 히어로 섹션 -->
<div class="hero-section">
    <div class="hero-content">
        <span class="hero-urgent">🔥 신청마감 D-3일</span>
        
        <p class="hero-sub">숨은 지원금 1분만에 찾기!</p>
        <h2 class="hero-title">
            나의 <span class="hero-highlight">숨은 지원금</span> 찾기
        </h2>
        <p class="hero-amount">신청자 <strong>1인 평균 127만원</strong> 수령</p>
        
        <a class="hero-cta" href="<?php echo esc_url($connect_url); ?>">
            30초만에 내 지원금 확인 <span class="cta-arrow">→</span>
        </a>
        
        <div class="hero-trust">
            <span class="trust-item">✓ 무료 조회</span>
            <span class="trust-item">✓ 30초 완료</span>
            <span class="trust-item">✓ 개인정보 보호</span>
        </div>
        
        <div class="hero-notice">
            <div class="notice-content">
                <div class="notice-title">💡신청 안하면 못 받아요</div>
                <p class="notice-desc">대한민국 92%가 놓치고 있는 정부 지원금, 지금 확인하고 혜택 놓치지 마세요!</p>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
