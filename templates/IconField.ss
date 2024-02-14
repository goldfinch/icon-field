<div
  class="goldfinchicon"
  data-goldfinch-icon-field="{$Name}"
  data-goldfinch-icon-config="{$IconsConfigJSON}"
  data-goldfinch-icon-source="{$SourceJSON}"
>

  <input type="hidden" name="$Name" id="$ID" value="$Value" data-goldfinch-icon-input>

  <div class="goldfinchicon__wrapper goldfinchicon__wrapper--selected" data-goldfinch-icon-selected>
    $CurrentIcons
    <%-- <ul>
      <% if $Value %>
      <li>
        <% if IconsConfig.type == 'dir' %>
        <i style="display: inline-block; width: 32px; height: 32px; mask-size: cover; mask-repeat: no-repeat; mask-position: center; background-color: #43536d; mask-image: url({$IconsConfig.source}/{$Value}.svg)"></i>
        <% else_if IconsConfig.type == 'font' %>
        <i class="$Value"></i>
        <% end_if %>
      </li>
      <% end_if %>
    </ul> --%>
  </div>

  <div data-goldfinch-icon-loader>
  <button type="button" class="btn btn-primary tool-button font-icon-down-circled">Load all icons</button>
  </div>

  <div class="field text goldfinchicon__search goldfinchicon__hide" data-goldfinch-icon-search>
    <input type="text" class="text" placeholder="Search icon ...">
  </div>

  <div class="goldfinchicon__wrapper goldfinchicon__wrapper--selections goldfinchicon__hide" data-goldfinch-icon-selection>
  </div>

  <%-- <div class="goldfinchicon__wrapper goldfinchicon__wrapper--selections js-goldfinchicon" data-goldfinch-icon-selection>
    <ul $AttributesHTML>
      <% loop $Options %>
      <li class="option">
        <input
          id="$ID"
          class="radio"
          name="$Name"
          type="radio"
          value="$Value"
          onchange="window.goldfinch.iconSelect(this)"
          <% if $isChecked %> checked<% end_if %>
        />
        <label for="$ID">
          <% if Value %>
            <i class="$Value"></i>
            $Up.SVG($Value)
            <img src="$Up.getFullRelativePath($Value)" />
          <% end_if %>
        </label>
      </li>
      <% end_loop %>
    </ul>
  </div> --%>

</div>
