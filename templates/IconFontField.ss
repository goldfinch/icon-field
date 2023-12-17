<div class="goldfinchicon">
  <div class="goldfinchicon__wrapper js-goldfinchicon">
    <ul $AttributesHTML>
      <% loop $Options %>
        <li class="option">
          <input
            id="$ID"
            class="radio"
            name="$Name"
            type="radio"
            value="$Value"
            <% if $isChecked %> checked<% end_if %>
          />
          <label for="$ID">
            <% if Value %>
              <i class="$Value"></i>
              <%-- $Up.SVG($Value) --%>
              <%-- <img src="$Up.getFullRelativePath($Value)" /> --%>
            <% end_if %>
          </label>
        </li>
      <% end_loop %>
    </ul>
  </div>
</div>
