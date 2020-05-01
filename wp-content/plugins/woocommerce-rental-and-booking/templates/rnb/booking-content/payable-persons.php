<div id="adultPreview" class="additional-person additional-person-adult rnb-select-wrapper rnb-component-wrapper rq-sidebar-select"></div>

<script type="text/html" id="adultBuilder">
    <% if(items.length){ %>
    <h5><%= title %></h5>
    <select class="additional_adults_info redq-select-boxes rnb-select-box person-select" name="additional_adults_info">
        <option value=""><%= placeholder %></option>
        <% _.each(items, function(item, index) { %>
        <% if(item.person_cost_applicable === 'per_day') { %>
        <option value="<%= item.id %>" data-unit="per_day" <% if( selectedItem && selectedItem === item.person_slug ){ %> selected <% } %>><%= item.person_count %> : <%= item.extra_meta %> </option>
        <option value="<%= item.id %>" data-unit="per_hour" style="display:none;" <% if( selectedItem && selectedItem === item.person_slug ){ %> selected <% } %>><%= item.person_count %> - <%= item.extra_hourly_meta %> </option>
        <% }else{ %>
        <option value="<%= item.id %>" <% if( selectedItem && selectedItem === item.person_slug ){ %> selected <% } %>><%= item.person_count %> : <%= item.extra_meta %> </option>
        <% } %>
        <% }) %>
    </select>
    <% } %>
</script>

<div id="childPreview" class="additional-person additional-person-child rnb-select-wrapper rnb-component-wrapper rq-sidebar-select"></div>

<script type="text/html" id="childBuilder">
    <% if(items.length){ %>
    <h5><%= title %></h5>
    <select class="additional_childs_info redq-select-boxes rnb-select-box person-select" name="additional_childs_info">
        <option value=""><%= placeholder %></option>
        <% _.each(items, function(item, index) { %>
        <% if(item.person_cost_applicable === 'per_day') { %>
        <option value="<%= item.id %>" data-unit="per_day" <% if( selectedItem && selectedItem === item.person_slug ){ %> selected <% } %>><%= item.person_count %> : <%= item.extra_meta %> </option>
        <option value="<%= item.id %>" data-unit="per_hour" style="display:none;" <% if( selectedItem && selectedItem === item.person_slug ){ %> selected <% } %>><%= item.person_count %> : <%= item.extra_hourly_meta %> </option>
        <% }else{ %>
        <option value="<%= item.id %>" <% if( selectedItem && selectedItem === item.person_slug ){ %> selected <% } %>><%= item.person_count %> : <%= item.extra_meta %> </option>
        <% } %>
        <% }) %>
    </select>
    <% } %>
</script>