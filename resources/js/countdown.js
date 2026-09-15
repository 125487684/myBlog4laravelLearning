/**
 * 倒计时按钮模块
 *
 * 用法（Blade）：
 *   <button type="submit"
 *       data-countdown
 *       data-countdown-seconds="60"
 *       data-countdown-label="重新发送验证邮件"
 *       data-countdown-template=":s 秒后可重发"
 *       ...>
 *       <span>初始文案</span>
 *   </button>
 *
 * 初始化后：按钮禁用，内部文本按 template 逐秒倒数，
 * 归零后恢复可点击并还原初始文案。
 */

export default function startCountdown(button) {
    // 0 表示当前无需冷却：直接不启动（parseInt 失败得 NaN，同样跳过）
    let seconds = parseInt(button.dataset.countdownSeconds, 10);
    if (!Number.isFinite(seconds) || seconds <= 0) {
        return;
    }

    const restoreLabel = button.dataset.countdownLabel || button.textContent.trim();
    const template = button.dataset.countdownTemplate || ':s';

    // label 放进 span（如 Blade 提供）；否则直接用按钮文本
    const labelEl = button.querySelector('span') || button;

    const tick = () => {
        if (seconds > 0) {
            button.disabled = true;
            labelEl.textContent = template.replace(':s', seconds);
            seconds -= 1;
            setTimeout(tick, 1000);
        } else {
            button.disabled = false;
            labelEl.textContent = restoreLabel;
        }
    };

    tick();
}

export function initCountdowns(root = document) {
    root.querySelectorAll('[data-countdown]').forEach((button) => {
        startCountdown(button);
    });
}