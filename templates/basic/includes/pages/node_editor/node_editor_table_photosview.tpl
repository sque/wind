{*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005-2014 	by WiND Contributors (see AUTHORS.txt)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *}
<form name="{$extra_data.FORM_NAME}" method="post" action="{$action_url}" enctype="multipart/form-data">
<input type="hidden" name="query_string" value="{$hidden_qs}" />
<input type="hidden" name="form_name" value="{$extra_data.FORM_NAME}" />
<table width="100%"  border="0" cellspacing="0" cellpadding="2">
{section name=row loop=$data}
{if $smarty.section.row.index == 0 }
<tr>
{else}
{if $smarty.section.row.index is not even}
<tr class="table-list-list1">
{else}
<tr class="table-list-list2">
{/if}
{/if}
	{foreach key=key item=cell from=$data[row]}
	{if $smarty.section.row.index == 0}
		{assign var="cellclass" value="table-list-top-cell"}
	{else}
		{assign var="cellclass" value="table-list-cell"}
	{/if}
	<td class="{$cellclass}">
	{if $key == 'photo' && $smarty.section.row.index>0}
	{if $cell != ''}<img src="{$cell}" />{else}<input class="fld-form-file" type="file" name="{$data[row].view_point}" value="{$lang.search}" alt="" />{/if}
	{elseif $key=='info' && $smarty.section.row.index>0}
	<textarea name="info-{$data[row].view_point}">{$cell|escape}</textarea>
	{elseif $smarty.section.row.index==0}
	{$lang.db.$cell}
	{elseif $key == 'date_in' && $smarty.section.row.index>0}
	{$cell|date_format:"%x"}
	{elseif $key == 'view_point' && $smarty.section.row.index>0}
	{assign var="vp" value="photos__view_point-$cell"}{$lang.db.$vp}
	{else}
	{$cell}	
	{/if}</td>
	{/foreach}
	{if $smarty.section.row.index>0}
	<td><input type="checkbox" name="id[]" value="{$extra_data.MULTICHOICE[row]}" {if $extra_data.MULTICHOICE_CHECKED[row] == 'YES'}checked="checked" {/if}/></td>
	{else}
	<td class="table-list-top-cell">{$lang[$extra_data.MULTICHOICE_LABEL]}</td>
	{/if}
</tr>
{/section}
<tr>
	{foreach key=key item=cell from=$data[0]}
	<td></td>
	{/foreach}
	<td><button class="btn btn-primary" type="submit">{$lang.update}</button></td>
</tr>
</table>
</form>
