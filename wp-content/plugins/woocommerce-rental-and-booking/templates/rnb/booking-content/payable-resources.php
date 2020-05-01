<div id="resourcePreview" class="payable-extras booking-section-single rnb-component-wrapper rq-sidebar-select"></div>

<script type="text/html" id="resourceBuilder">
    <% if(items.length){ %>
        <h5><%= title %></h5>
        <% _.each(items, function(item, index) { %>
        <div class="attributes">
            <label class="custom-block">
                <input type="checkbox" name="extras[]" value="<%= item.id %>" class="booking-extra" <% if( selectedItems && selectedItems.includes(item.resource_slug)){ %> checked <% } %>>
                <%= item.resource_name %> <%= item.extra_meta %>
            </label>
        </div>
        <% }) %>
    <% } %>
</script>