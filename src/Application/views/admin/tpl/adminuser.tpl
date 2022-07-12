[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
</form>

[{if $recipient}]
    <form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
        <input type="hidden" name="fnc" value="">
        <input type="hidden" name="oxid" value="[{$oxid}]">

        <table style="border: 0; width: 98%; padding: 0; border-spacing: 0">
            <tr>
                <!-- Anfang linke Seite -->
                <td style="text-align: left; width: 100%; vertical-align: top;" class="edittext">
                    <table style="border: 0; padding: 0; border-spacing: 0">
                        <tr>
                            <td class="edittext">
                                <label for="recipient">[{oxmultilang ident="D3LM_ADMIN_USER_RECIPIENT"}]</label>
                            </td>
                            <td class="edittext">
                                <input type="text" id="recipient" name="recipient" class="editinput" size="60" value="[{$recipient}]" readonly disabled>
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext">
                                <label for="messagebody">[{oxmultilang ident="D3LM_ADMIN_USER_MESSAGE"}]</label>
                            </td>
                            <td class="edittext">
                                <textarea id="messagebody" name="messagebody" class="editinput" cols="60" rows="5" [{$readonly}]></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext">
                            </td>
                            <td class="edittext"><br>
                                <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="D3LM_ADMIN_SEND"}]" onClick="document.myedit.fnc.value='send'"" [{$readonly}]>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </form>
[{/if}]

[{include file="bottomitem.tpl"}]