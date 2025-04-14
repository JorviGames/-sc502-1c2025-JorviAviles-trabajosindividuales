document.addEventListener('DOMContentLoaded', function () {
    const TASKS_API = "backend/tasks.php";
    const COMMENTS_API = "backend/comments.php";
    let isEditMode = false;
    let editingId = null;
    let tasks = [];

    // Cargar tareas y sus comentarios
    async function loadTasks() {
        try {
            const response = await fetch(TASKS_API, { method: 'GET', credentials: 'include' });
            if (response.ok) {
                tasks = await response.json();
                await loadCommentsForTasks();
                renderTasks();
            } else {
                if (response.status === 401) {
                    window.location.href = "index.html";
                }
                console.error("Error al obtener tareas");
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Cargar los comentarios de cada tarea
    async function loadCommentsForTasks() {
        for (let task of tasks) {
            try {
                const res = await fetch(`${COMMENTS_API}?task_id=${task.id}`);
                if (res.ok) {
                    const comments = await res.json();
                    task.comments = comments;
                } else {
                    task.comments = [];
                }
            } catch (err) {
                console.error(`Error al cargar comentarios para la tarea ${task.id}`, err);
                task.comments = [];
            }
        }
    }

    // Renderizar tareas y sus comentarios
    function renderTasks() {
        const taskList = document.getElementById('task-list');
        taskList.innerHTML = '';
        tasks.forEach(task => {
            let commentsList = '';
            if (task.comments && task.comments.length > 0) {
                commentsList = '<ul class="list-group list-group-flush">';
                task.comments.forEach(comment => {
                    commentsList += `
                        <li class="list-group-item">
                            ${comment.description}
                            <button type="button" class="btn btn-sm btn-link remove-comment" data-taskid="${task.id}" data-commentid="${comment.id}">
                                Remove
                            </button>
                        </li>`;
                });
                commentsList += '</ul>';
            }
            const taskCard = document.createElement('div');
            taskCard.className = 'col-md-4 mb-3';
            taskCard.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${task.title}</h5>
                    <p class="card-text">${task.description}</p>
                    <p class="card-text"><small class="text-muted">Due: ${task.due_date}</small></p>
                    ${commentsList}
                    <button type="button" class="btn btn-sm btn-link add-comment" data-taskid="${task.id}">
                        Add Comment
                    </button>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button class="btn btn-secondary btn-sm edit-task" data-taskid="${task.id}">Edit</button>
                    <button class="btn btn-danger btn-sm delete-task" data-taskid="${task.id}">Delete</button>
                </div>
            </div>`;
            taskList.appendChild(taskCard);
        });

        // Eventos para botones recién creados
        document.querySelectorAll('.edit-task').forEach(button => {
            button.addEventListener('click', handleEditTask);
        });
        document.querySelectorAll('.delete-task').forEach(button => {
            button.addEventListener('click', handleDeleteTask);
        });
        document.querySelectorAll('.add-comment').forEach(button => {
            button.addEventListener('click', function (e) {
                const taskId = e.currentTarget.getAttribute('data-taskid');
                document.getElementById('comment-task-id').value = taskId;
                const modal = new bootstrap.Modal(document.getElementById('commentModal'));
                modal.show();
            });
        });
        document.querySelectorAll('.remove-comment').forEach(button => {
            button.addEventListener('click', async function (e) {
                const taskId = e.currentTarget.getAttribute('data-taskid');
                const commentId = e.currentTarget.getAttribute('data-commentid');
                try {
                    const res = await fetch(`${COMMENTS_API}?task_id=${taskId}&comment_id=${commentId}`, {
                        method: 'DELETE',
                        credentials: 'include'
                    });
                    if (res.ok) {
                        loadTasks();
                    } else {
                        console.error("Error al eliminar el comentario");
                    }
                } catch (err) {
                    console.error(err);
                }
            });
        });
    }

    // Manejador para editar tarea
    function handleEditTask(e) {
        try {
            const taskId = parseInt(e.currentTarget.getAttribute('data-taskid'));
            const task = tasks.find(t => t.id === taskId);
            if (!task) return;
            document.getElementById('task-title').value = task.title;
            document.getElementById('task-desc').value = task.description;
            document.getElementById('due-date').value = task.due_date;
            isEditMode = true;
            editingId = taskId;
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            modal.show();
        } catch (error) {
            console.error("Error al intentar editar la tarea:", error);
        }
    }

    // Manejador para eliminar tarea
    async function handleDeleteTask(e) {
        const taskId = e.currentTarget.getAttribute('data-taskid');
        console.log("Intentando eliminar tarea con id:", taskId);
        try {
            // Usamos ?id= en lugar de ?task_id=
            const res = await fetch(`${TASKS_API}?id=${taskId}`, {
                method: 'DELETE',
                credentials: 'include'
            });
            console.log("Respuesta DELETE:", res.status);
            if (res.ok) {
                console.log("Tarea eliminada correctamente");
                loadTasks();
            } else {
                const errorJson = await res.json();
                console.error("Error al eliminar la tarea:", errorJson);
            }
        } catch (err) {
            console.error("Error en la petición DELETE:", err);
        }
    }
    

    // Envío del formulario de comentarios
    document.getElementById('comment-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const comment = document.getElementById('comment-text').value;
        const taskId = document.getElementById('comment-task-id').value;
        try {
            const res = await fetch(COMMENTS_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_id: parseInt(taskId), description: comment }),
                credentials: 'include'
            });
            if (res.ok) {
                loadTasks();
            } else {
                console.error("Error al agregar el comentario");
            }
        } catch (err) {
            console.error(err);
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('commentModal'));
        modal.hide();
    });

    // Envío del formulario de tareas (agregar/editar)
    document.getElementById('task-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const title = document.getElementById('task-title').value;
        const description = document.getElementById('task-desc').value;
        const dueDate = document.getElementById('due-date').value;
        if (isEditMode) {
            try {
                const res = await fetch(`${TASKS_API}?task_id=${editingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title, description, due_date: dueDate }),
                    credentials: 'include'
                });
                if (!res.ok) {
                    console.error("No se pudo actualizar la tarea");
                }
            } catch (err) {
                console.error(err);
            }
        } else {
            try {
                const res = await fetch(TASKS_API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title, description, due_date: dueDate }),
                    credentials: 'include'
                });
                if (!res.ok) {
                    console.error("No se pudo agregar la tarea");
                }
            } catch (err) {
                console.error(err);
            }
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('taskModal'));
        modal.hide();
        loadTasks();
    });

    // Resetear formularios al mostrar los modales
    document.getElementById('commentModal').addEventListener('show.bs.modal', function () {
        document.getElementById('comment-form').reset();
    });
    document.getElementById('taskModal').addEventListener('show.bs.modal', function () {
        if (!isEditMode) {
            document.getElementById('task-form').reset();
        }
    });
    document.getElementById('taskModal').addEventListener('hidden.bs.modal', function () {
        editingId = null;
        isEditMode = false;
    });

    loadTasks();
});
