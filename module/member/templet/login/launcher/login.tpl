{$formStart}
<div class="MemberLoginPortal">
	<div class="innerLayer">
		<table cellpadding="0" cellspacing="0" class="layoutfixed">
		<col width="108" /><col width="60" /><col width="1" /><col width="100%" />
		<tr>
			<td colspan="2">
				<span class="bold pointer" onclick="OpenURL('http://www.ndoor.co.kr{$link.signin}');">회원가입</span> | <span class="pointer" onclick="OpenURL('http://www.ndoor.co.kr{$link.help}');">아이디/패스워드 찾기</span>
			</td>
			<td style="background:#E5E5E5;"></td>
			<td rowspan="3">
				<div class="height25 center">
					<span class="f11">보안</span> <span id="sStep" class="f14 tahoma bold">1</span> <span class="f11">단계</span>
				</div>
				
				<div id="sButton" class="securityButton step1">
					<div onclick="SecurityStep(1);"></div>
					<div onclick="SecurityStep(2);"></div>
					<div onclick="SecurityStep(3);"></div>
				</div>
				
				<div class="height10"></div>
				
				<div class="center f11">
					IP보안 <span id="sIP" class="ipon" onclick="SecurityIP(this)">ON</span>
				</div>
			</td>
		</tr>
		<tr class="height5">
			<td colspan="2"></td>
			<td style="background:#E5E5E5;"></td>
		</tr>
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" class="layoutfixed">
				<col width="20" /><col width="100%" />
				<tr>
					<td colspan="2" class="user_idarea"><input type="text" name="user_id" class="inputbox" msg="아이디를 입력하여 주십시오." onfocus="SetBackgroundText(this,false);" onblur="SetBackgroundText(this,true);"/></td>
				</tr>
				<tr>
					<td colspan="2" class="passwordarea"><input type="password" name="password" class="inputbox" msg="패스워드를 입력하여 주십시오." onfocus="SetBackgroundText(this,false);" onblur="SetBackgroundText(this,true);" /></td>
				</tr>
				<tr class="height5">
					<td colspan="2"></td>
				</tr>
				<tr>
					<td><input type="checkbox" class="checkbox" id="autologin" name="autologin" value="1" onclick="MemberLoginAutoLogin(this.id);" msg="공용컴퓨터에서 자동로그인을 설정할 경우, 개인정보가 유출될 수 있습니다.<br />자동로그인을 하도록 설정하시겠습니까?" /></td>
					<td><label for="autologin" onclick="MemberLoginAutoLogin('autologin');">로그인 상태유지</label></td>
				</tr>
				</table>
				
			</td>
			<td class="vTop"><input type="image" src="{$skinDir}/images/btn_login.gif" /></td>
			<td style="background:#E5E5E5;"></td>
		</tr>
		</table>
	</div>
</div>
{$formEnd}