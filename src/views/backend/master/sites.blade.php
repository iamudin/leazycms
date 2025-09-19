@extends('cms::backend.layout.app', ['title' => 'Monitor Situs'])

@section('content')
          <h2>Monitor Situs</h2>
          <button id="refreshBtn" class="btn btn-sm btn-primary">Segarkan</button>
          <p id="meta" class="text-muted small">Memuat...</p>

          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Situs</th>
                <th>Maintenance</th>
                <th>Editor</th>
                <th>User</th>
                <th>Modules</th>
                <th style="min-width:100px">HTTP/Time</th>
              </tr>
            </thead>
            <tbody id="statusTable">
              <tr><td colspan="6" class="text-muted">Loading...</td></tr>
            </tbody>
          </table>
    <script>
    function loadData(url = '{{ route('app.master.fetch') }}') {
      $("#statusTable").html('<tr><td colspan="6" class="text-muted">Loading...</td></tr>');

      $.getJSON(url, function(resp){
        $("#meta").text(`Generated: ${resp.meta.generated_at} • Count: ${resp.meta.count}`);
        let rows = '';
        resp.data.forEach(item => {
          rows += `<tr data-id="${item.id}">
            <td>${item.domain}</td>
            <td>
              <button class="btn-toggle-maintenance btn btn-sm ${item.maintenance ? 'btn-success' : 'btn-danger'}" 
                      data-status="${item.maintenance ? 1 : 0}">
                ${item.maintenance ? 'ON' : 'OFF'}
              </button>
            </td>
            <td>
              <button class="btn-toggle-editor btn btn-sm ${item.editor_template_enabled ? 'btn-primary' : 'btn-secondary'}" 
                      data-status="${item.editor_template_enabled ? 1 : 0}">
                ${item.editor_template_enabled ? 'ENABLED' : 'DISABLED'}
              </button>
            </td>
            <td>${item.user_count ?? '-'}</td>
            <td>${(item.active_modules || []).join(', ')}</td>
            <td>${item.http_code} / ${(item.time || 0).toFixed(2)}s</td>
          </tr>`;
        });
        $("#statusTable").html(rows);

        // bind toggle maintenance
        $(".btn-toggle-maintenance").click(function() {
          let row = $(this).closest("tr");
          let id = row.data("id");
          let status = $(this).data("status");
          toggleAction('maintenance', id, status, this);
        });

        // bind toggle editor template
        $(".btn-toggle-editor").click(function() {
          let row = $(this).closest("tr");
          let id = row.data("id");
          let status = $(this).data("status");
          toggleAction('editor', id, status, this);
        });
      });
    }

    function toggleAction(type, id, status, btn) {
      $(btn).prop("disabled", true).text("Processing...");

      $.getJSON(`{{route('app.master.update')}}`, { 
          id: id, 
          status: status,
          type: type
      }, function(resp) {
        if(resp.success) {
          loadData(); // refresh tabel
        } else {
          alert("Gagal update status!");
          $(btn).prop("disabled", false).text("Retry");
        }
      }).fail(()=>{
        alert("Error request ke server");
        $(btn).prop("disabled", false).text("Retry");
      });
    }

    $("#refreshBtn").click(()=> loadData('{{ route('app.master.refresh') }}'));
    loadData();
    </script>

@endsection
