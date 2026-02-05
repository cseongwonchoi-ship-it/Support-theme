<?php
/**
 * 지원금 테마 Functions
 * 
 * @package Support_Theme
 */

// 직접 접근 방지
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 테마 설정
 */
function support_theme_setup() {
    // 타이틀 태그 지원
    add_theme_support('title-tag');
    
    // 피처드 이미지 지원
    add_theme_support('post-thumbnails');
    
    // HTML5 지원
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
}
add_action('after_setup_theme', 'support_theme_setup');

/**
 * 스크립트 및 스타일 로드
 */
function support_theme_scripts() {
    // 스타일시트
    wp_enqueue_style('support-theme-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // 구글 폰트
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap', array(), null);
    
    // Custom JavaScript
    wp_enqueue_script('support-theme-custom', get_template_directory_uri() . '/custom.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'support_theme_scripts');

/**
 * ========================================
 * 관리자 설정 페이지
 * ========================================
 */
function support_theme_admin_menu() {
    add_menu_page(
        '지원금 테마 설정',
        '지원금 설정',
        'manage_options',
        'support-theme-settings',
        'support_theme_settings_page',
        'dashicons-admin-generic',
        60
    );
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
        
        // AI 카드 생성
        if (isset($_POST['support_theme_generate_card']) && !empty($_POST['ai_keyword'])) {
            $result = support_theme_generate_card($_POST['ai_keyword']);
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>✅ AI 지원금 카드가 생성되었습니다!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>❌ AI 카드 생성에 실패했습니다. 키워드를 확인해주세요.</p></div>';
            }
        }
        
        // 수동 카드 추가
        if (isset($_POST['support_theme_add_manual_card'])) {
            $result = support_theme_add_manual_card();
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>✅ 카드가 추가되었습니다!</p></div>';
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
                <!-- 헤더 제목 -->
                <tr>
                    <th scope="row"><label for="header_title">헤더 제목</label></th>
                    <td>
                        <input type="text" id="header_title" name="header_title" 
                               value="<?php echo esc_attr(get_option('support_theme_header_title', '지원금 스킨')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                
                <!-- 로고 이미지 URL -->
                <tr>
                    <th scope="row"><label for="logo_url">로고 이미지 URL</label></th>
                    <td>
                        <input type="url" id="logo_url" name="logo_url" 
                               value="<?php echo esc_url(get_option('support_theme_logo_url', '')); ?>" 
                               class="regular-text">
                        <p class="description">로고 이미지 URL을 입력하세요</p>
                    </td>
                </tr>
                
                <!-- 연결 URL -->
                <tr>
                    <th scope="row"><label for="connect_url">연결할 URL</label></th>
                    <td>
                        <input type="url" id="connect_url" name="connect_url" 
                               value="<?php echo esc_url(get_option('support_theme_connect_url', home_url())); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                
                <!-- 애드센스 광고 코드 -->
                <tr>
                    <th scope="row"><label for="ad_code">광고 코드 (애드센스 등)</label></th>
                    <td>
                        <textarea id="ad_code" name="ad_code" rows="5" class="large-text code"><?php echo esc_textarea(get_option('support_theme_ad_code', '')); ?></textarea>
                        <p class="description">애드센스 광고 코드나 다른 광고 코드를 붙여넣으세요</p>
                    </td>
                </tr>
            </table>
            
            <h2>탭 메뉴 설정 (최대 3개)</h2>
            <table class="form-table">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <tr>
                    <th scope="row">탭 <?php echo $i; ?></th>
                    <td>
                        <input type="text" name="tab_name_<?php echo $i; ?>" 
                               value="<?php echo esc_attr(get_option("support_theme_tab_name_$i", '')); ?>" 
                               placeholder="탭 이름" style="width: 200px;">
                        
                        <input type="url" name="tab_link_<?php echo $i; ?>" 
                               value="<?php echo esc_url(get_option("support_theme_tab_link_$i", '')); ?>" 
                               placeholder="링크 URL" style="width: 300px;">
                        
                        <label>
                            <input type="radio" name="tab_active" value="<?php echo $i; ?>" 
                                   <?php checked(get_option('support_theme_tab_active', '1'), $i); ?>>
                            Active
                        </label>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>
            
            <?php submit_button('설정 저장', 'primary', 'support_theme_save'); ?>
        </form>
        
        <hr style="margin: 40px 0;">
        
        <h2>📝 지원금 카드 관리</h2>
        
        <!-- AI 카드 생성 -->
        <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3>🤖 AI로 지원금 카드 자동 생성</h3>
            <p>키워드를 입력하면 AI가 자동으로 지원금 카드 정보를 생성합니다.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('support_theme_save_action', 'support_theme_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="ai_keyword">키워드</label></th>
                        <td>
                            <input type="text" id="ai_keyword" name="ai_keyword" 
                                   placeholder="예: 청년도약계좌" class="regular-text" required>
                            <p class="description">생성하고 싶은 지원금 키워드를 입력하세요</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('🚀 AI로 카드 생성', 'secondary', 'support_theme_generate_card'); ?>
            </form>
        </div>
        
        <!-- 수동 카드 추가 -->
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px;">
            <h3>➕ 수동으로 카드 추가</h3>
            
            <form method="post" action="">
                <?php wp_nonce_field('support_theme_save_action', 'support_theme_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="card_keyword">키워드 (카드 제목) <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_keyword" name="card_keyword" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_amount">금액/혜택 강조 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_amount" name="card_amount" placeholder="예: 최대 4.5% 금리" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_amount_sub">부가 설명 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_amount_sub" name="card_amount_sub" placeholder="예: 비과세 + 대출 우대" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_description">한 줄 설명 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_description" name="card_description" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="card_target">지원대상 (20자 이내) <span style="color:red;">*</span></label></th>
                        <td>
                            <input type="text" id="card_target" name="card_target" maxlength="20" class="regular-text" required>
                            <p class="description">⚠️ 반드시 20자 이내로 입력해주세요!</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="card_period">신청시기 <span style="color:red;">*</span></label></th>
                        <td><input type="text" id="card_period" name="card_period" placeholder="예: 상시" class="regular-text" required></td>
                    </tr>
                </table>
                
                <?php submit_button('➕ 수동으로 카드 추가', 'secondary', 'support_theme_add_manual_card'); ?>
            </form>
        </div>
        
        <!-- 기존 카드 목록 -->
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
                        <th>금액/혜택</th>
                        <th>지원대상</th>
                        <th>신청시기</th>
                        <th style="width: 100px;">삭제</th>
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
                                <button type="submit" name="support_theme_delete_card" class="button button-small" 
                                        onclick="return confirm('정말 삭제하시겠습니까?');">🗑️ 삭제</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 40px; text-align: center;">
                <p style="font-size: 18px; color: #666;">📭 등록된 카드가 없습니다.</p>
                <p style="color: #999;">위에서 AI 생성 또는 수동으로 카드를 추가해주세요.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
        .form-table th {
            width: 200px;
        }
        .notice {
            margin: 15px 0;
        }
    </style>
    <?php
}

/**
 * 기본 설정 저장
 */
function support_theme_save_settings() {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    // 헤더 제목
    if (isset($_POST['header_title'])) {
        update_option('support_theme_header_title', sanitize_text_field($_POST['header_title']));
    }
    
    // 로고 URL
    if (isset($_POST['logo_url'])) {
        update_option('support_theme_logo_url', esc_url_raw($_POST['logo_url']));
    }
    
    // 연결 URL
    if (isset($_POST['connect_url'])) {
        update_option('support_theme_connect_url', esc_url_raw($_POST['connect_url']));
    }
    
    // 광고 코드
    if (isset($_POST['ad_code'])) {
        update_option('support_theme_ad_code', wp_kses_post($_POST['ad_code']));
    }
    
    // 탭 메뉴
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_POST["tab_name_$i"])) {
            update_option("support_theme_tab_name_$i", sanitize_text_field($_POST["tab_name_$i"]));
        }
        if (isset($_POST["tab_link_$i"])) {
            update_option("support_theme_tab_link_$i", esc_url_raw($_POST["tab_link_$i"]));
        }
    }
    
    if (isset($_POST['tab_active'])) {
        update_option('support_theme_tab_active', sanitize_text_field($_POST['tab_active']));
    }
    
    return true;
}

/**
 * 수동 카드 추가
 */
function support_theme_add_manual_card() {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    // 필수 필드 확인
    if (empty($_POST['card_keyword']) || empty($_POST['card_amount']) || 
        empty($_POST['card_amount_sub']) || empty($_POST['card_description']) || 
        empty($_POST['card_target']) || empty($_POST['card_period'])) {
        return false;
    }
    
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
 * AI로 카드 생성
 */
function support_theme_generate_card($keyword) {
    if (!current_user_can('manage_options') || empty($keyword)) {
        return false;
    }
    
    $keyword = sanitize_text_field($keyword);
    
    // Claude API 호출
    $api_url = 'https://api.anthropic.com/v1/messages';
    
    $prompt = "다음 키워드에 대해 후킹성 있고 정확한 카드 내용을 만들어줘.

키워드: {$keyword}

다음 형식의 JSON으로만 답변해:
{
  \"keyword\": \"키워드명\",
  \"amount\": \"금액/혜택 강조 (예: 최대 4.5% 금리, 월 50만원)\",
  \"amountSub\": \"부가 설명 (예: 비과세 + 대출 우대)\",
  \"description\": \"한 줄 설명\",
  \"target\": \"지원대상 (20자 이내 필수)\",
  \"period\": \"신청시기 (예: 상시)\"
}

주의사항:
- 실제 정책 정보 기반
- target은 반드시 20자 이내
- JSON만 출력";
    
    $body = array(
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1000,
        'messages' => array(
            array(
                'role' => 'user',
                'content' => $prompt
            )
        )
    );
    
    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($body),
        'timeout' => 30,
    ));
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);
    
    if (isset($data['content'][0]['text'])) {
        $json_text = $data['content'][0]['text'];
        $json_text = preg_replace('/```json\n?/', '', $json_text);
        $json_text = preg_replace('/```\n?$/', '', $json_text);
        $json_text = trim($json_text);
        
        $card_data = json_decode($json_text, true);
        
        if ($card_data && isset($card_data['keyword'])) {
            $cards = get_option('support_theme_cards', array());
            $cards[] = $card_data;
            update_option('support_theme_cards', $cards);
            return true;
        }
    }
    
    return false;
}

/**
 * 카드 삭제
 */
function support_theme_delete_card($index) {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    $cards = get_option('support_theme_cards', array());
    
    if (isset($cards[$index])) {
        unset($cards[$index]);
        $cards = array_values($cards); // 인덱스 재정렬
        update_option('support_theme_cards', $cards);
        return true;
    }
    
    return false;
}

/**
 * 카드 데이터 가져오기
 */
function support_theme_get_cards() {
    return get_option('support_theme_cards', array());
}

/**
 * 헤더 제목 가져오기
 */
function support_theme_get_header_title() {
    return get_option('support_theme_header_title', '지원금 스킨');
}

/**
 * 로고 URL 가져오기
 */
function support_theme_get_logo_url() {
    $logo = get_option('support_theme_logo_url', '');
    if (empty($logo)) {
        return 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEhwxd_YGfZiM_d9LPozylA_vt2w36-eanzKSgvMQm2zkh-s41pKzT2FDyyqB9cz713Tm3nRFVbtRR8GGXlEQh7UDr4BDteEwfQ_JDV0Yl_xYA5uBGWrqyhDLH_PNEa9cJmNLOhhFc7XKAJChRiR9_6KZbraUo8FpA2IGMxbgMNGAtnoi-WlBnWYpnm0FKw/w945-h600-p-k-no-nu/img.png';
    }
    return $logo;
}

/**
 * 연결 URL 가져오기
 */
function support_theme_get_connect_url() {
    return get_option('support_theme_connect_url', home_url());
}

/**
 * 광고 코드 가져오기
 */
function support_theme_get_ad_code() {
    return get_option('support_theme_ad_code', '');
}

/**
 * 탭 메뉴 데이터 가져오기
 */
function support_theme_get_tabs() {
    $tabs = array();
    
    for ($i = 1; $i <= 3; $i++) {
        $name = get_option("support_theme_tab_name_$i", '');
        if (!empty($name)) {
            $tabs[] = array(
                'name' => $name,
                'link' => get_option("support_theme_tab_link_$i", home_url()),
                'active' => (get_option('support_theme_tab_active', '1') == $i)
            );
        }
    }
    
    return $tabs;
}
