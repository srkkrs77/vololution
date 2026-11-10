/* RU: Основной контейнер | EN: Main container */
.cc-banner {
    position: fixed; 
    z-index: 99999; 
    max-width: 600px; 
    background: #fff; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
    border-radius: 8px; 
    padding: 20px; 
    font-family: sans-serif; 
    transition: opacity 0.3s, transform 0.3s; 
    opacity: 1; 
    transform: translateY(0);
}

/* RU: Скрытое состояние | EN: Hidden state */
.cc-banner.hidden { 
    opacity: 0; 
    transform: translateY(20px); 
    pointer-events: none; 
}

/* RU: Позиционирование (управляется параметром &position=... в сниппете) | EN: Positioning (controlled by &position=... param in snippet) */
.cc-bottom-left { bottom: 20px; left: 20px; }
.cc-bottom-right { bottom: 20px; right: 20px; }
.cc-bottom-center { bottom: 20px; left: 50%; transform: translateX(-50%); }
.cc-bottom-center.hidden { transform: translateX(-50%) translateY(20px); }

/* RU: Тексты | EN: Texts */
.cc-banner h3 { margin: 0 0 10px; font-size: 18px; color: #333; }
.cc-banner p { margin: 0 0 15px; font-size: 14px; color: #666; line-height: 1.5; }
.cc-banner a { color: #0066cc; text-decoration: underline; }

/* RU: Кнопки | EN: Buttons */
.cc-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; }
.cc-actions button { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500; transition: background 0.2s; }
.cc-btn-all { background: #222; color: #fff; }
.cc-btn-all:hover { background: #444; }
.cc-btn-ess { background: #eee; color: #333; }
.cc-btn-ess:hover { background: #ddd; }
.cc-btn-set { background: transparent; color: #666; text-decoration: underline; }

/* RU: Детали настроек | EN: Settings details */
.cc-details { display: none; margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px; }
.cc-details.open { display: block; }
.cc-details label { display: block; margin-bottom: 8px; font-size: 14px; cursor: pointer; }
.cc-details .disabled { opacity: 0.5; cursor: not-allowed; }
.cc-btn-save { background: #222; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px; }
.cc-btn-save:hover { background: #444; }