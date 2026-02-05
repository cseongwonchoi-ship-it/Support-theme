<?php
/**
 * 지원금 테마 Functions (Puter.js 적용 버전)
 * * @package Support_Theme
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 테마 설정 및 스크립트 로드
 */
function support_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
}
add_action('after_setup_theme', 'support_theme_setup');

function support_theme_scripts() {
    wp_enqueue_style('support-theme-style', get_stylesheet_uri(), array(), '1.0.0');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap', array(), null);
    wp_enqueue_script('support-theme-custom', get_template_directory_uri() . '/custom.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'support_theme_scripts');

/**
 * ========================================
 * 관리자 설정 페이지 (Puter.js 스크립트 추가)
 * ========================================
 */
function support_theme_admin_assets($hook) {
    // 설정 페이지에서만 로드
    if ($hook != 'toplevel_page_support-theme-settings') {
        return;
    }
    // Puter.js 라이브러리 로드
    wp_enqueue_script('puter-js', 'https://js.puter.com/v2/', array(), null, true);
}
add_action('admin_enqueue_scripts', 'support_theme_admin_assets');

function support_theme_admin_menu() {
    add_menu_page('지원금 테마 설정', '지원금 설정', 'manage_options', 'support-theme-settings', 'support_theme_settings_page', 'dashicons-admin-generic', 60);
}
add_action('admin_menu', 'support_theme_admin_menu');

/**
 * 설정 페이지 HTML
 */
function support_theme_settings_page() {
    // 폼 제출 처리
    if (isset($_POST['support_theme_nonce']) && wp_verify_nonce($_POST['support_theme_nonce'], 'support_theme_save_action')) {
        
        // 기본 설정 저장
        if (isset($_POST['support_theme_save'])) {
            support_theme_save_settings();
            echo '<div class="notice notice-success is-dismissible"><p>✅ 설정이 저장되었습니다!</p></div>';
        }
        
        // 카드 추가 (AI가 채워준 내용을 저장)
        if (isset($_POST['support_theme_add_manual_card'])) {
            $result = support_theme_add_manual_card();
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>✅ 지원금 카드가 추가되었습니다!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>❌ 모든 필드를 입력해주세요.</p></div>';
            }
        }
        
        // 카드 삭제
        if (isset($_POST['support_theme_delete_card']) && isset($_POST['card_index'])) {
            support_theme_delete_card($_POST['card_index']);
            echo '<div class="notice notice-success is-dismissible"><p>✅ 카드가 삭제되었습니다!</p></div>';
        }
    }
    ?>
    
    <div class="wrap">
        <h1>🎯 지원금 테마 설정</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('support_theme_save_action', 'support_theme_nonce'); ?>
            
            <h2>기본 설정</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="header_title">헤더 제목</label></th>
                    <td><input type="text" name="header_title" value="<?php echo esc_attr(get_option('support_theme_header_title', '지원금 스킨')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="logo_url">로고 URL</label></th>
                    <td><input type="url" name="logo_url" value="<?php echo esc_url(get_option('support_theme_logo_url', '')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="connect_url">연결 URL</label></th>
                    <td><input type="url" name="connect_url" value="<?php echo esc_url(get_option('support_theme_connect_url', home_url())); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="ad_code">광고 코드</label></th>
                    <td><textarea name="ad_code" rows="3" class="large-text code"><?php echo esc_textarea(get_option('support_theme_ad_code', '')); ?></textarea></td>
                </tr>
            </table>
            
            <h3>탭 메뉴 설정</h3>
            <table class="form-table">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <tr>
                    <th scope="row">탭 <?php echo $i; ?></th>
                    <td>
                        <input type="text" name="tab_name_<?php echo $i; ?>" value="<?php echo esc_attr(get_option("support_theme_tab_name_$i", '')); ?>" placeholder="이름">
                        <input type="url" name="tab_link_<?php echo $i; ?>" value="<?php echo esc_url(get_option("support_theme_tab_link_$i", '')); ?>" placeholder="URL">
                        <label><input type="radio" name="tab_active" value="<?php echo $i; ?>" <?php checked(get_option('support_theme_tab_active', '1'), $i); ?>> Active</label>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>
            <?php submit_button('기본 설정 저장', 'primary', 'support_theme_save'); ?>
        </form>
        
        <hr style="margin: 40px 0;">
        
        <h2>📝 지원금 카드 관리</h2>
        
        <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; margin-bottom: 30px; border:1px solid #cce5ff;">
            <h3>🤖 Puter AI로 내용 생성</h3>
            <p>키워드를 입력하고 <b>[AI 생성]</b> 버튼을 누르면, 아래 <b>[수동으로 카드 추가]</b> 입력창에 내용이 자동으로 채워집니다.</p>
            
            <table class="form-table">
                <tr>
                    <th><label for="ai_keyword">키워드 입력</label></th>
                    <td>
                        <input type="text" id="ai_keyword" placeholder="예: 청년도약계좌" class="regular-text">
                        <button type="button" id="btn_generate_ai" class="button button-secondary">✨ AI 내용 생성하기</button>
                        <span id="ai_loading" style="display:none; margin-left:10px; color:#0073aa;">⏳ AI가 내용을 작성 중입니다... (약 3~5초 소요)</span>
                    </td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px;">
            <h3>➕ 카드 추가 (AI가 내용을 채워줍니다)</h3>
            
            <form method="post" action="">
                <?php wp_nonce_field('support_theme_save_action', 'support_theme_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="card_keyword">키워드 (제목) <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_keyword" name="card_keyword" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_amount">금액/혜택 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_amount" name="card_amount" placeholder="예: 최대 50만원" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_amount_sub">부가 설명 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_amount_sub" name="card_amount_sub" placeholder="예: 매월 지급" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_description">한 줄 설명 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_description" name="card_description" class="large-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_target">지원대상 (20자) <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_target" name="card_target" maxlength="20" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_period">신청시기 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_period" name="card_period" placeholder="예: 상시" class="regular-text" required></td>
                    </tr>
                </table>
                
                <?php submit_button('💾 확인 후 카드 저장', 'primary', 'support_theme_add_manual_card'); ?>
            </form>
        </div>

        <h3>📋 등록된 지원금 카드 (<?php echo count(get_option('support_theme_cards', array())); ?>개)</h3>
        <?php
        $cards = get_option('support_theme_cards', array());
        if (!empty($cards)):
        ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">번호</th>
                        <th>키워드</th>
                        <th>금액</th>
                        <th>대상</th>
                        <th>시기</th>
                        <th style="width: 80px;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cards as $index => $card): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><strong><?php echo esc_html($card['keyword']); ?></strong></td>
                        <td><?php echo esc_html($card['amount']); ?></td>
                        <td><?php echo esc_html($card['target']); ?></td>
                        <td><?php echo esc_html($card['period']); ?></td>
                        <td>
                            <form method="post" action="" style="display: inline;">
                                <?php wp_nonce_field('support_theme_save_action', 'support_theme_nonce'); ?>
                                <input type="hidden" name="card_index" value="<?php echo $index; ?>">
                                <button type="submit" name="support_theme_delete_card" class="button button-small" onclick="return confirm('삭제하시겠습니까?');">삭제</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="description">등록된 카드가 없습니다.</p>
        <?php endif; ?>
    </div>

    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const generateBtn = document.getElementById('btn_generate_ai');
        const loading = document.getElementById('ai_loading');
        
        if(generateBtn) {
            generateBtn.addEventListener('click', async function() {
                const keyword = document.getElementById('ai_keyword').value;
                if(!keyword) {
                    alert('키워드를 입력해주세요!');
                    return;
                }
                
                // 로딩 표시
                generateBtn.disabled = true;
                loading.style.display = 'inline-block';
                
                const prompt = `
                    '${keyword}'에 대한 지원금/정책 정보를 아래 JSON 형식으로 정확하게 만들어줘.
                    한국 실정에 맞는 실제 정보여야 해.
                    
                    형식:
                    {
                        "keyword": "${keyword}",
                        "amount": "핵심 혜택 (예: 월 50만원, 최대 4.5%)",
                        "amountSub": "부가 혜택 (예: 12개월 지급, 비과세)",
                        "description": "정책에 대한 매력적인 한 줄 요약 설명",
                        "target": "지원 대상 (핵심만 20자 이내로 짧게)",
                        "period": "신청 기간 (예: 2024년 상시, 별도 공고시)"
                    }
                    
                    오직 JSON 데이터만 출력해. 마크다운이나 코드블럭 없이 순수 JSON만.
                `;

                try {
                    // Puter AI 호출
                    const response = await puter.ai.chat(prompt);
                    
                    // 응답 전처리 (혹시 모를 마크다운 제거)
                    let cleanJson = response.message || response; // 구조에 따라 다를 수 있음
                    if (typeof cleanJson !== 'string') {
                        cleanJson = cleanJson.content || response.toString();
                    }
                    
                    cleanJson = cleanJson.replace(/```json/g, '').replace(/```/g, '').trim();
                    
                    const data = JSON.parse(cleanJson);
                    
                    // 입력 필드에 자동 채우기
                    document.getElementById('card_keyword').value = data.keyword || keyword;
                    document.getElementById('card_amount').value = data.amount || '';
                    document.getElementById('card_amount_sub').value = data.amountSub || '';
                    document.getElementById('card_description').value = data.description || '';
                    document.getElementById('card_target').value = data.target || '';
                    document.getElementById('card_period').value = data.period || '';
                    
                    alert('✅ AI가 정보를 생성했습니다! 내용을 확인하고 저장 버튼을 눌러주세요.');
                    
                } catch (error) {
                    console.error('AI Error:', error);
                    alert('❌ 생성 중 오류가 발생했습니다. 잠시 후 다시 시도하거나 수동으로 입력해주세요.');
                } finally {
                    generateBtn.disabled = false;
                    loading.style.display = 'none';
                }
            });
        }
    });
    </script>
    <?php
}

/**
 * 설정 저장 (기본)
 */
function support_theme_save_settings() {
    if (!current_user_can('manage_options')) return;
    
    if (isset($_POST['header_title'])) update_option('support_theme_header_title', sanitize_text_field($_POST['header_title']));
    if (isset($_POST['logo_url'])) update_option('support_theme_logo_url', esc_url_raw($_POST['logo_url']));
    if (isset($_POST['connect_url'])) update_option('support_theme_connect_url', esc_url_raw($_POST['connect_url']));
    if (isset($_POST['ad_code'])) update_option('support_theme_ad_code', wp_kses_post($_POST['ad_code']));
    
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_POST["tab_name_$i"])) update_option("support_theme_tab_name_$i", sanitize_text_field($_POST["tab_name_$i"]));
        if (isset($_POST["tab_link_$i"])) update_option("support_theme_tab_link_$i", esc_url_raw($_POST["tab_link_$i"]));
    }
    if (isset($_POST['tab_active'])) update_option('support_theme_tab_active', sanitize_text_field($_POST['tab_active']));
}

/**
 * 카드 수동 추가 (AI가 채워준 폼을 처리)
 */
function support_theme_add_manual_card() {
    if (!current_user_can('manage_options')) return false;
    
    // 필수값 체크
    if (empty($_POST['card_keyword']) || empty($_POST['card_amount'])) return false;
    
    $cards = get_option('support_theme_cards', array());
    $new_card = array(
        'keyword' => sanitize_text_field($_POST['card_keyword']),
        'amount' => sanitize_text_field($_POST['card_amount']),
        'amountSub' => sanitize_text_field($_POST['card_amount_sub']),
        'description' => sanitize_text_field($_POST['card_description']),
        'target' => sanitize_text_field($_POST['card_target']),
        'period' => sanitize_text_field($_POST['card_period']),
    );
    
    $cards[] = $new_card;
    update_option('support_theme_cards', $cards);
    return true;
}

/**
 * 카드 삭제
 */
function support_theme_delete_card($index) {
    if (!current_user_can('manage_options')) return false;
    
    $cards = get_option('support_theme_cards', array());
    if (isset($cards[$index])) {
        unset($cards[$index]);
        $cards = array_values($cards);
        update_option('support_theme_cards', $cards);
        return true;
    }
    return false;
}
?>
