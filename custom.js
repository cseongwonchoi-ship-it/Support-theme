/**
 * 지원금 테마 Custom JavaScript
 * 
 * @package Support_Theme
 */

(function() {
    'use strict';

    // 이탈 방지 팝업 관련 변수
    var popupShown = sessionStorage.getItem('exitPopupShown');
    var closeCount = parseInt(sessionStorage.getItem('exitPopupCloseCount')) || 0;
    var scrollTriggered = false;

    /**
     * 페이지 로드 시 초기화
     */
    window.addEventListener('load', function() {
        initExitPopup();
        initSmoothScroll();
        initCardAnimations();
    });

    /**
     * 이탈 방지 팝업 초기화
     */
    function initExitPopup() {
        // PC: 마우스 이탈 감지
        document.addEventListener('mouseout', function(e) {
            e = e || window.event;
            var y = e.clientY;
            
            // 마우스가 화면 상단을 벗어날 때
            if (y < 0 && !popupShown && closeCount < 2) {
                showPopup();
            }
        });
        
        // PC + 모바일: 뒤로가기 감지
        history.pushState(null, '', location.href);
        window.addEventListener('popstate', function() {
            if (closeCount < 2) {
                showPopup();
            }
            history.pushState(null, '', location.href);
        });
        
        // 모바일: 스크롤 60% 도달 시 팝업
        window.addEventListener('scroll', function() {
            var scrollHeight = document.body.scrollHeight - window.innerHeight;
            var scrollPercent = (window.scrollY / scrollHeight) * 100;
            
            if (scrollPercent > 60 && !popupShown && !scrollTriggered && closeCount < 2) {
                showPopup();
                scrollTriggered = true;
            }
        });
    }

    /**
     * 팝업 표시
     */
    window.showPopup = function() {
        var popup = document.getElementById('exitPopup');
        if (popup) {
            popup.style.display = 'flex';
        }
    };

    /**
     * 팝업 닫기
     */
    window.closePopup = function() {
        var popup = document.getElementById('exitPopup');
        if (popup) {
            popup.style.display = 'none';
        }
    };

    /**
     * 팝업 닫고 스크롤
     */
    window.closePopupAndScroll = function() {
        closePopup();
        var hero = document.querySelector('.hero-section');
        if (hero) {
            hero.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    };

    /**
     * 팝업 닫기 (나중에)
     */
    window.closePopupNotNow = function() {
        closePopup();
        popupShown = true;
        closeCount++;
        sessionStorage.setItem('exitPopupShown', 'true');
        sessionStorage.setItem('exitPopupCloseCount', closeCount.toString());
    };

    /**
     * 부드러운 스크롤 초기화
     */
    function initSmoothScroll() {
        // 내부 링크 클릭 시 부드러운 스크롤
        var links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                var href = this.getAttribute('href');
                
                if (href === '#') return;
                
                var target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * 카드 애니메이션 초기화
     */
    function initCardAnimations() {
        var cards = document.querySelectorAll('.info-card');
        
        if (cards.length === 0) return;
        
        // Intersection Observer로 뷰포트 진입 시 애니메이션
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });
            
            cards.forEach(function(card, index) {
                // 초기 상태 설정
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.transitionDelay = (index * 0.1) + 's';
                
                observer.observe(card);
            });
        } else {
            // Intersection Observer 미지원 브라우저는 바로 표시
            cards.forEach(function(card) {
                card.style.opacity = '1';
            });
        }
    }

    /**
     * 탭 활성화 상태 유지
     */
    function initTabState() {
        var tabs = document.querySelectorAll('.tab-link');
        var currentUrl = window.location.href;
        
        tabs.forEach(function(tab) {
            var tabUrl = tab.getAttribute('href');
            
            if (currentUrl.indexOf(tabUrl) !== -1) {
                // 모든 탭에서 active 제거
                tabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                
                // 현재 탭에 active 추가
                tab.classList.add('active');
            }
        });
    }

    /**
     * 이미지 레이지 로딩
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            var images = document.querySelectorAll('img[data-src]');
            
            var imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.getAttribute('data-src');
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * 헤더 스크롤 효과
     */
    function initHeaderScroll() {
        var header = document.getElementById('header');
        var lastScroll = 0;
        
        if (!header) return;
        
        window.addEventListener('scroll', function() {
            var currentScroll = window.scrollY;
            
            if (currentScroll > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    }

    /**
     * 폼 유효성 검사
     */
    function initFormValidation() {
        var forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var inputs = form.querySelectorAll('input[required], textarea[required]');
                var isValid = true;
                
                inputs.forEach(function(input) {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('error');
                        
                        // 에러 메시지 표시
                        var errorMsg = input.nextElementSibling;
                        if (!errorMsg || !errorMsg.classList.contains('error-message')) {
                            errorMsg = document.createElement('span');
                            errorMsg.className = 'error-message';
                            errorMsg.style.color = 'red';
                            errorMsg.style.fontSize = '12px';
                            errorMsg.textContent = '이 필드는 필수입니다.';
                            input.parentNode.insertBefore(errorMsg, input.nextSibling);
                        }
                    } else {
                        input.classList.remove('error');
                        var errorMsg = input.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('error-message')) {
                            errorMsg.remove();
                        }
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * 모바일 메뉴 토글
     */
    function initMobileMenu() {
        var menuToggle = document.querySelector('.mobile-menu-toggle');
        var menu = document.querySelector('.tabs');
        
        if (menuToggle && menu) {
            menuToggle.addEventListener('click', function() {
                menu.classList.toggle('active');
                this.classList.toggle('active');
            });
        }
    }

    /**
     * 클릭 이벤트 추적 (선택적)
     */
    function initClickTracking() {
        var cards = document.querySelectorAll('.info-card');
        
        cards.forEach(function(card) {
            card.addEventListener('click', function() {
                var cardTitle = this.querySelector('.info-card-title');
                if (cardTitle) {
                    console.log('카드 클릭:', cardTitle.textContent);
                    
                    // Google Analytics가 있다면
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'card_click', {
                            'event_category': 'engagement',
                            'event_label': cardTitle.textContent
                        });
                    }
                }
            });
        });
    }

    /**
     * 페이지 로드 완료 후 추가 초기화
     */
    document.addEventListener('DOMContentLoaded', function() {
        initTabState();
        initLazyLoading();
        initHeaderScroll();
        initFormValidation();
        initMobileMenu();
        initClickTracking();
    });

    /**
     * 리사이즈 이벤트 디바운스
     */
    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // 리사이즈 후 실행할 코드
            console.log('Window resized');
        }, 250);
    });

    /**
     * 콘솔 경고 (개발자 도구 오픈 시)
     */
    console.log('%c⚠️ 경고!', 'color: red; font-size: 24px; font-weight: bold;');
    console.log('%c이 콘솔을 사용하여 무언가를 붙여넣으면 공격자가 귀하의 계정에 접근할 수 있습니다.', 'font-size: 16px;');
    console.log('%c테마 제작: 아로스 (https://aros100.com)', 'color: blue; font-size: 14px;');

})();
