<div id="depositPreview" class="payable-security_deposites booking-section-single rnb-component-wrapper rq-sidebar-select"></div>

<script type="text/html" id="depositBuilder">
    <% if(items.length){ %>
        <h5><%= title %></h5>
        <% _.each(items, function(item, index) { %>
        <div class="attributes">
            <label class="custom-block">
                <input type="checkbox" name="security_deposites[]" value="<%= item.id %>" class="booking-extra" <% if(item.security_deposite_clickable === 'no'){ %> checked readonly onclick="return false" <% } %> <% if( selectedItems && selectedItems.includes(item.security_deposite_slug)){ %> checked <% } %> />
                <%= item.security_deposite_name %> <%= item.extra_meta %>
            </label>
        </div> <% }) %>
    <% } %>
</script>