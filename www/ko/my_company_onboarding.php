<?php
if (!defined('_GNUBOARD_')) exit;
?>
<style>
	#pageTop, #sVisual, #sub-visual { display: none; }
	.company-connect-wrap { max-width: 72rem; margin: 7.2rem auto 9.6rem; padding: 0 2rem; }
	.company-connect-card { background: #fff; }
	.company-connect-badge { display: flex; align-items: center; width: max-content; max-width: 100%; height: 2.8rem; margin: 0 auto; padding: 0 1rem; border-radius: .4rem; background: #f4f6f8; color: #667085; font-size: 1.3rem; font-weight: 700; box-sizing: border-box; }
	.company-connect-title { margin: 1.8rem 0 .8rem; color: #111827; font-size: 2.8rem; line-height: 1.35; font-weight: 800; }
	.company-connect-desc { margin: 0 0 2.8rem; color: #667085; font-size: 1.5rem; line-height: 1.65; }
	.company-connect-form { display: flex; flex-direction: column; gap: 1.8rem; }
	.company-connect-field label { display: block; margin-bottom: .8rem; color: #344054; font-size: 1.4rem; font-weight: 700; }
	.company-connect-field label::after { content: '*'; padding-left: .3rem; color: #f00; }
	.company-connect-cert { display: flex; gap: .8rem; align-items: center; }
	.company-connect-input { width: 100%; height: 5.2rem; border: 1px solid #d0d5dd; border-radius: .6rem; padding: 0 1.5rem; color: #101828; font-size: 1.6rem; background: #fff; box-sizing: border-box; }
	.company-connect-input:read-only { background: #f9fafb; color: #667085; }
	.company-connect-cert .company-connect-input { flex: 1; min-width: 0; }
	.company-connect-cert-btn { height: 5.2rem; padding: 0 1.8rem; border: 0; border-radius: .6rem; background: #111827; color: #fff; font-size: 1.5rem; font-weight: 800; white-space: nowrap; cursor: pointer; }
	.company-connect-cert-btn:disabled { background: #9ca3af; cursor: not-allowed; }
	.company-connect-help, .company-connect-status { margin: .8rem 0 0; color: #8a94a6; font-size: 1.3rem; line-height: 1.5; }
	.company-connect-status.success { color: #2563eb; }
	.company-connect-status.error { color: #d92d20; }
	.company-connect-actions { display: flex; margin-top: 1.2rem; }
	.company-connect-submit { display: flex; align-items: center; justify-content: center; width: 100%; height: 6rem; border: 0; border-radius: .5rem; background: var(--point); color: #fff; font-size: 1.7rem; line-height: 2rem; font-weight: 550; cursor: pointer; box-sizing: border-box; }
	.company-connect-submit:disabled { background: #9ca3af; cursor: not-allowed; }
	@media (max-width: 640px) {
		.company-connect-wrap { margin: 3.6rem auto 6.4rem; padding: 0 1.6rem; }
		.company-connect-title { font-size: 2.4rem; }
		.company-connect-cert { display: block; }
		.company-connect-cert-btn { width: 100%; margin-top: .8rem; }
	}
</style>

<div class="company-connect-wrap">
	<div class="company-connect-card member_sign_layout">
		<span class="company-connect-badge">내 회사 정보</span>
		<h4 class="company-connect-title member_sign_title">회사 정보를 등록해 주세요</h4>
		<p class="company-connect-desc">사업자등록번호를 확인하면 KODATA 기업 정보가 자동으로 연결됩니다.</p>

		<form class="company-connect-form" autocomplete="off" onsubmit="return false;">
			<div class="company-connect-field">
				<label for="company-connect-bizno">사업자등록번호</label>
				<div class="company-connect-cert">
					<input class="company-connect-input" type="text" id="company-connect-bizno" inputmode="numeric" maxlength="12" placeholder="000-00-00000">
					<button class="company-connect-cert-btn" type="button" id="company-connect-preview-button">확인</button>
				</div>
				<p class="company-connect-help">사업자등록번호 확인 후 법인명이 자동으로 입력됩니다.</p>
				<p class="company-connect-status" id="company-connect-status" aria-live="polite"></p>
			</div>

			<div class="company-connect-field">
				<label for="company-connect-name">법인명</label>
				<input class="company-connect-input" type="text" id="company-connect-name" placeholder="사업자등록번호를 먼저 확인해 주세요" readonly>
			</div>

			<div class="company-connect-actions">
				<button class="company-connect-submit" type="button" id="company-connect-confirm-button" disabled>내 회사 정보 확인하기</button>
			</div>
		</form>
	</div>
</div>

<script>
(function() {
	var csrfToken = <?php echo json_encode($my_company_connect_token, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
	var input = document.getElementById("company-connect-bizno");
	var companyName = document.getElementById("company-connect-name");
	var previewButton = document.getElementById("company-connect-preview-button");
	var confirmButton = document.getElementById("company-connect-confirm-button");
	var status = document.getElementById("company-connect-status");
	var verifiedBizno = "";

	function formatBizno(value) {
		var digits = String(value || "").replace(/[^0-9]/g, "").slice(0, 10);
		if (digits.length <= 3) return digits;
		if (digits.length <= 5) return digits.slice(0, 3) + "-" + digits.slice(3);
		return digits.slice(0, 3) + "-" + digits.slice(3, 5) + "-" + digits.slice(5);
	}

	function setStatus(message, type) {
		status.className = "company-connect-status" + (type ? " " + type : "");
		status.textContent = message || "";
	}

	function resetVerification() {
		verifiedBizno = "";
		companyName.value = "";
		confirmButton.disabled = true;
		setStatus("", "");
	}

	function setPending(button, pending, label) {
		button.disabled = pending;
		button.textContent = pending ? "확인 중..." : label;
	}

	function request(action, bizno) {
		return fetch("/ko/my_company_connect.php", {
			method: "POST",
			headers: {"Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"},
			body: new URLSearchParams({action: action, bizno: bizno, csrf_token: csrfToken}).toString()
		}).then(function(response) {
			return response.json().catch(function() { return {}; }).then(function(data) {
				if (!response.ok || !data.success) throw new Error(data.message || "처리 중 오류가 발생했습니다.");
				return data;
			});
		});
	}

	input.addEventListener("input", function() {
		this.value = formatBizno(this.value);
		resetVerification();
	});

	previewButton.addEventListener("click", function() {
		var bizno = input.value.replace(/[^0-9]/g, "");
		if (bizno.length !== 10) {
			setStatus("사업자등록번호 10자리를 확인해 주세요.", "error");
			input.focus();
			return;
		}

		setPending(previewButton, true, "확인");
		resetVerification();
		request("preview", bizno).then(function(data) {
			verifiedBizno = bizno;
			companyName.value = data.company.company || "";
			confirmButton.disabled = !companyName.value;
			setStatus("사업자등록번호 확인이 완료되었습니다.", "success");
		}).catch(function(error) {
			setStatus(error.message, "error");
		}).finally(function() {
			setPending(previewButton, false, "확인");
		});
	});

	confirmButton.addEventListener("click", function() {
		if (!verifiedBizno || !companyName.value) return;

		setPending(confirmButton, true, "내 회사 정보 확인하기");
		setStatus("", "");
		request("connect", verifiedBizno).then(function(data) {
			window.location.href = data.redirect || "/account/company";
		}).catch(function(error) {
			setStatus(error.message, "error");
			setPending(confirmButton, false, "내 회사 정보 확인하기");
		});
	});
})();
</script>
