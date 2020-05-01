<div id="pickupLocationPreview" class="redq-pick-up-location rnb-select-wrapper rnb-component-wrapper rq-sidebar-select"></div>

<script type="text/html" id="pickupLocationBuilder">
    <% if(items.length){ %>
        <h5><%= title %></h5>
        <select class="redq-select-boxes pickup_location rnb-select-box" name="pickup_location">
            <option value=""><%= placeholder %></option>
            <% _.each(items, function(item, index) { %>
            <option value="<%= item.id %>" <% if( selectedItem && selectedItem === item.slug ){ %> selected <% } %>><%= item.title %></option>
            <% }) %>
        </select>
    <% } %>
</script>