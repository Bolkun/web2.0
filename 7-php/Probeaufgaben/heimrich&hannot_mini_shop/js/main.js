function deleteTask(data) {
    $.ajax({
        url: 'form/deleteTask.php',
        type: "POST",
        data: data,
        dataType: "text",
        error: error,
        success: success
    });
}
function error() {
    alert('Error by data loading!');
}
function success(data) {
    $('#tasks').load(document.URL +  ' #tasks');
}

