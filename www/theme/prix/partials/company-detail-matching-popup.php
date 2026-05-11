<?php
if (!defined('_GNUBOARD_')) exit;

$company_detail_popup_variant = isset($company_detail_popup_variant) ? $company_detail_popup_variant : 'simple';
$company_detail_credit_cnt = isset($msinfo['matching_able_cnt']) ? (int)$msinfo['matching_able_cnt'] : 0;
if ($company_detail_credit_cnt === 0 && isset($member['mb_id']) && $member['mb_id']) {
    $uid_sql = sql_real_escape_string((string)$member['mb_id']);
    $status_row = sql_fetch("select matching_able_cnt from g5_membership_status where uid='{$uid_sql}'");
    if (isset($status_row['matching_able_cnt'])) {
        $company_detail_credit_cnt = (int)$status_row['matching_able_cnt'];
    }
}
$company_detail_sender = trim(
    (isset($member['mb_name']) ? $member['mb_name'] : '') . ' ' .
    (isset($member['mb_6']) ? $member['mb_6'] : '') . ' ' .
    (isset($member['mb_7']) ? $member['mb_7'] : '')
);
if ($company_detail_sender === '') $company_detail_sender = '로그인 사용자';
?>

<style>
.popup.popup_submit .popupInner{
    max-width:calc(90rem + 2px);
    max-height:calc(100% - 4rem);
    min-height:min(76rem, calc(100% - 4rem));
    padding:3.5rem 5rem;
    border:1px solid #e6e6e6;
}
@media only screen and (max-width: 480px){
    .popup.popup_submit .popupInner{
        min-height:auto;
        max-height:calc(100% - 4rem);
        padding:3.5rem 2rem;
    }
}
</style>

<?php if ($company_detail_popup_variant === 'advanced') { ?>
<style>
.req-ui-box{border:1px solid #dfe5f3;border-radius:12px;padding:12px;margin:12px 0;background:#fbfcff}
.req-ui-title{font-size:14px;font-weight:700;color:#222;margin:0 0 8px}
.req-role-wrap{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.req-role-wrap label.biz-role-btn{display:block;position:relative;cursor:pointer}
.req-role-wrap label.biz-role-btn input{position:absolute;opacity:0;pointer-events:none}
.req-role-wrap label.biz-role-btn span{
    display:inline-flex;align-items:center;justify-content:center;
    min-width:92px;padding:10px 14px;border:1px solid #d9dce3;border-radius:8px;
    background:#fff;color:#333;font-size:14px;font-weight:600;line-height:1;
    transition:border-color .15s ease, background-color .15s ease, color .15s ease;
}
.req-role-wrap label.biz-role-btn input:checked + span{border-color:#3856ff;background:#3856ff;color:#fff}
.req-role-wrap label.biz-role-btn input:focus + span{box-shadow:0 0 0 3px rgba(56,86,255,.18)}
.req-tpl-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.req-tpl-card{border:1px solid #d9dfec;border-radius:10px;padding:10px;cursor:pointer;background:#fff}
.req-tpl-card.is-on{border-color:#2f67ff;background:#f3f7ff}
.req-tpl-card h6{margin:0 0 6px;font-size:14px;color:#1f2a44}
.req-tpl-card p{margin:0;font-size:13px;line-height:1.45;color:#4a587c}
.req-tpl-card .req-tpl-head{display:flex;gap:8px;align-items:center}
.req-tpl-card .req-tpl-head input{margin:0}
.req-required-note{margin-top:8px;padding:8px 10px;border:1px solid #d7e1ff;border-radius:8px;background:#eef3ff;font-size:12px;line-height:1.45;color:#4b5b88}
.req-required-note strong{color:#1d3fbf;font-weight:700}
@media (max-width: 767px){.req-tpl-grid{grid-template-columns:1fr}}
</style>
<div class="popup popup_submit">
    <div class="popupInner">
        <a href="javascript:popupClose('')" class="popupClose">
            <img src="/img/icon/icon_close.svg" alt="닫기">
        </a>

        <div class="popup_top">
            <h4 class="popup_top_title">매칭요청</h4>
            <div class="popup_top_macthing_length">
                남은 <span class="point">크레딧 수 <b class="length"><?= $company_detail_credit_cnt ?></b> 크레딧</span>
            </div>
        </div>

        <div class="popup_content">
            <table>
                <tbody>
                    <tr>
                        <th>보내는 사람</th>
                        <td><?= $company_detail_sender ?></td>
                    </tr>
                    <tr>
                        <th>받는 사람</th>
                        <td id="recv">신규 거래처 담당자</td>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="type"  value="matching">
            <input type="hidden" name="kedcd" value="" id="kedcd">
            <input type="hidden" name="typ"   value="accept">
            <input type="hidden" name="request_type" id="request_type" value="BASIC">
            <div style="margin:16px 0 12px;">
                <strong style="display:block;margin-bottom:8px;">요청 방식 선택</strong>
                <div id="requestTypeWrap" style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="requestTypeBtn is-active" data-value="BASIC" style="border:1px solid #3856ff;background:#3856ff;color:#fff;padding:10px 12px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">기본 요청 (10 크레딧)</button>
                    <button type="button" class="requestTypeBtn" data-value="TM" style="border:1px solid #d9dce3;background:#fff;color:#333;padding:10px 12px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">TM 요청 (30 크레딧)</button>
                </div>
                <p id="requestTypeDesc" style="margin-top:8px;font-size:13px;color:#666;">기본 요청은 상대 기업에 매칭 요청만 전달되며 1건당 10크레딧이 차감됩니다.</p>
            </div>
            <div class="req-ui-box">
                <p class="req-ui-title">기업 유형 선택 (필수)</p>
                <div class="req-role-wrap">
                    <label class="biz-role-btn"><input type="radio" name="biz_role_ui" value="manufacturer" <?php echo (isset($member['mb_3']) && $member['mb_3'] === '제조사') ? 'checked' : ''; ?>><span>제조사</span></label>
                    <label class="biz-role-btn"><input type="radio" name="biz_role_ui" value="distributor" <?php echo (isset($member['mb_3']) && $member['mb_3'] === '제조사') ? '' : 'checked'; ?>><span>유통사</span></label>
                </div>
            </div>
            <div class="req-ui-box">
                <p class="req-ui-title">매칭 메시지 선택 (선택)</p>
                <div id="reqTplGrid" class="req-tpl-grid"></div>
                <p class="popup_textarea_tip" style="margin-top:8px;">* 예시 문구는 복수 선택 가능합니다.</p>
                <p class="req-required-note"><strong>입력 기준</strong> 매칭 메시지 선택과 추가 요청사항은 각각 선택 항목이며, 둘 중 하나 이상은 필수입니다.</p>
            </div>
            <textarea id="match_content_extra" placeholder="추가 요청사항을 입력하세요 (선택)"></textarea>
            <textarea name="match_content" id="match_content" style="display:none;"></textarea>
        </div>

        <div class="popup_btns">
            <button type="button" onclick="popupSubmit()">매칭요청</button>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="popup popup_submit">
    <div class="popupInner">
        <a href="javascript:popupClose()" class="popupClose"><img src="/img/icon/icon_close.svg" alt=""></a>
        <div class="popup_top">
            <h4 class="popup_top_title">매칭요청</h4>
            <div class="popup_top_macthing_length">남은 매칭 <span class="point">가능 수 <b class="length"><?= $company_detail_credit_cnt ?></b>개</span></div>
        </div>
        <div class="popup_content">
            <table>
                <tbody>
                    <tr>
                        <th>보내는 사람</th>
                        <td><?= $company_detail_sender ?></td>
                    </tr>
                    <tr>
                        <th>받는 사람</th>
                        <td class="receiver-name">신규 거래처 담당자</td>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="type" value="matching">
            <input type="hidden" name="kedcd" value="" id="kedcd">
            <input type="hidden" name="request_type" value="BASIC">
            <textarea name="match_content" id="match_content" placeholder="거래에 관한 간단한 요청사항을 입력해 주세요."></textarea>
            <p class="popup_textarea_tip">* 입력 내용은 매칭 요청 시 상대 기업에 전달됩니다.</p>
        </div>
        <div class="popup_btns">
            <button type="button" onclick="popupSubmit()">매칭요청</button>
        </div>
    </div>
</div>
<?php } ?>
