<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="main-wrapper">
    <!-- 헤더 섹션 -->
    <div id="header">
        <div class="container">
            <header class="header">
                <div class="container">
                    <div class="logo">
                        <?php 
                        $logo_url = support_theme_get_logo_url();
                        if (!empty($logo_url)):
                        ?>
                        <img alt="<?php bloginfo('name'); ?> 로고" src="<?php echo esc_url($logo_url); ?>"/>
                        <?php endif; ?>
                    </div>       
                    <h1 class="logo-text">
                        <a href="<?php echo esc_url(home_url('/')); ?>" style="color: inherit; text-decoration: none;">
                            <?php echo esc_html(support_theme_get_header_title()); ?>
                        </a>
                    </h1>
                </div>
            </header>
        </div>
    </div>

    <!-- 탭 메뉴 -->
    <div class="tab-wrapper">
        <div class="container">
            <nav class="tab-container">
                <ul class="tabs">
                    <?php
                    $tabs = support_theme_get_tabs();
                    if (!empty($tabs)):
                        foreach ($tabs as $tab):
                    ?>
                    <li class="tab-item">
                        <a class="tab-link<?php echo $tab['active'] ? ' active' : ''; ?>" 
                           href="<?php echo esc_url($tab['link']); ?>">
                            <?php echo esc_html($tab['name']); ?>
                        </a>
                    </li>
                    <?php 
                        endforeach;
                    else:
                        // 기본 탭 (탭이 설정되지 않은 경우)
                    ?>
                    <li class="tab-item">
                        <a class="tab-link active" href="<?php echo esc_url(home_url('/')); ?>">
                            홈
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- 메인 컨텐츠 시작 -->
    <div class="container">
