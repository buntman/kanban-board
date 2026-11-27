import React, { useState } from "react";
import { Link, router, useForm } from "@inertiajs/react";
import TaskForm from "./Partials/TaskForm";
import TaskItem from "./Partials/Item";
import CardHeader from "./Partials/CardHeader";
import ConfirmDialog from "@/Components/ConfirmDialog";
import TaskDetailsModal from "./Partials/TaskDetailsModal";

export default function Show({ project }) {
    const [isCreatingCard, setIsCreatingCard] = useState(false);
    const [cardToDelete, setCardToDelete] = useState(null);
    const [isDeletingCard, setIsDeletingCard] = useState(false);
    const [taskToDelete, setTaskToDelete] = useState(null);
    const [isDeletingTask, setIsDeletingTask] = useState(false);
    const [taskToEdit, setTaskToEdit] = useState(null);
    const [draggedTask, setDraggedTask] = useState(null);
    const [dropTarget, setDropTarget] = useState(null);
    const [isMoving, setIsMoving] = useState(false);
    const [draggedCard, setDraggedCard] = useState(null);
    const [cardDropTarget, setCardDropTarget] = useState(null);
    const [isMovingCard, setIsMovingCard] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: "",
    });

    const handleCreateCard = (e) => {
        e.preventDefault();

        post(route("project-cards.store", project.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset(); // Clears the input field
                setIsCreatingCard(false); // Closes the form popup
            },
        });
    };

    const deleteCard = () => {
        if (!cardToDelete) {
            return;
        }

        router.delete(route("project-cards.destroy", cardToDelete.id), {
            preserveScroll: true,
            onStart: () => setIsDeletingCard(true),
            onFinish: () => setIsDeletingCard(false),
            onSuccess: () => setCardToDelete(null),
        });
    };

    const deleteTask = () => {
        if (!taskToDelete) {
            return;
        }

        router.delete(route("tasks.destroy", taskToDelete.id), {
            preserveScroll: true,
            onStart: () => setIsDeletingTask(true),
            onFinish: () => setIsDeletingTask(false),
            onSuccess: () => setTaskToDelete(null),
        });
    };

    const handleDragStart = (event, task) => {
        event.dataTransfer.effectAllowed = "move";
        event.dataTransfer.setData("text/plain", String(task.id));
        setDraggedTask(task);
    };

    const handleDragEnd = () => {
        setDraggedTask(null);
        setDropTarget(null);
    };

    const handleTaskDragOver = (event, card, task) => {
        if (draggedCard) {
            return;
        }

        event.preventDefault();

        const rect = event.currentTarget.getBoundingClientRect();
        const after = event.clientY > rect.top + rect.height / 2;

        setDropTarget({
            cardId: card.id,
            taskId: task.id,
            after,
        });
    };

    const moveTask = (card, targetIndex) => {
        if (!draggedTask || isMoving) {
            return;
        }

        const cardTasks = card.tasks ?? [];
        const fromThisCard = draggedTask.project_card_id === card.id;
        const draggedIndex = fromThisCard
            ? cardTasks.findIndex((task) => task.id === draggedTask.id)
            : -1;

        let position = targetIndex;

        if (fromThisCard && draggedIndex < targetIndex) {
            position -= 1;
        }

        setIsMoving(true);

        router.patch(
            route("tasks.move", draggedTask.id),
            {
                project_card_id: card.id,
                position,
            },
            {
                preserveScroll: true,
                onFinish: () => {
                    setIsMoving(false);
                    setDraggedTask(null);
                    setDropTarget(null);
                },
            }
        );
    };

    const handleDropOnTask = (event, card, index) => {
        event.preventDefault();

        if (draggedCard) {
            return;
        }

        event.stopPropagation();

        const rect = event.currentTarget.getBoundingClientRect();
        const after = event.clientY > rect.top + rect.height / 2;

        moveTask(card, index + (after ? 1 : 0));
    };

    const handleDropOnColumn = (event, card) => {
        event.preventDefault();
        moveTask(card, card.tasks?.length ?? 0);
    };

    const handleCardDragStart = (event, card) => {
        event.dataTransfer.effectAllowed = "move";
        event.dataTransfer.setData("text/plain", String(card.id));
        setDraggedCard(card);
    };

    const handleCardDragEnd = () => {
        setDraggedCard(null);
        setCardDropTarget(null);
    };

    const handleColumnDragOver = (event, card) => {
        if (!draggedCard || isMovingCard) {
            return;
        }

        event.preventDefault();

        const rect = event.currentTarget.getBoundingClientRect();
        const after = event.clientX > rect.left + rect.width / 2;

        setCardDropTarget({ cardId: card.id, after });
    };

    const moveCard = (cardToMove, targetIndex) => {
        if (!cardToMove || isMovingCard) {
            return;
        }

        const projectCards = project.project_cards ?? [];
        const draggedIndex = projectCards.findIndex(
            (projectCard) => projectCard.id === cardToMove.id
        );

        let position = targetIndex;

        if (draggedIndex < targetIndex) {
            position -= 1;
        }

        setIsMovingCard(true);

        router.patch(
            route("project-cards.move", cardToMove.id),
            { position },
            {
                preserveScroll: true,
                onFinish: () => {
                    setIsMovingCard(false);
                    setDraggedCard(null);
                    setCardDropTarget(null);
                },
            }
        );
    };

    const handleColumnDrop = (event, card, index) => {
        if (!draggedCard || isMovingCard) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const rect = event.currentTarget.getBoundingClientRect();
        const after = event.clientX > rect.left + rect.width / 2;

        moveCard(draggedCard, index + (after ? 1 : 0));
    };

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            {/* TOP HEADER */}
            <header className="bg-white border-b px-8 py-4 flex justify-between items-center shadow-sm">
                <div className="flex items-center space-x-4">
                    <Link
                        href={route("projects.index")}
                        className="text-gray-500 hover:text-gray-700 text-sm font-medium"
                    >
                        &larr; Back to Projects
                    </Link>
                    <h1 className="text-2xl font-bold text-gray-800">
                        {project.name}
                    </h1>
                </div>
                <div className="text-sm text-gray-500">
                    {project.description || "Kanban Workspace"}
                </div>
            </header>

            {/* KANBAN BOARD CONTAINER (Horizontal Scroll) */}
            <main className="flex-1 p-8 overflow-x-auto flex items-start space-x-6">
                {/* MAP THROUGH PROJECT CARDS (COLUMNS) */}
                {project.project_cards &&
                    project.project_cards.map((card, index) => (
                        <div
                            key={card.id}
                            onDragOver={(event) =>
                                handleColumnDragOver(event, card)
                            }
                            onDrop={(event) =>
                                handleColumnDrop(event, card, index)
                            }
                            className={`w-80 bg-gray-100 rounded-xl p-4 flex-shrink-0 flex flex-col max-h-[80vh] shadow-sm border transition ${
                                draggedCard?.id === card.id ? "opacity-40" : ""
                            } ${
                                cardDropTarget?.cardId === card.id &&
                                !cardDropTarget.after
                                    ? "border-l-4 border-indigo-500"
                                    : ""
                            } ${
                                cardDropTarget?.cardId === card.id &&
                                cardDropTarget.after
                                    ? "border-r-4 border-indigo-500"
                                    : ""
                            }`}
                        >
                            {/* Column Header */}
                            <CardHeader
                                card={card}
                                onDelete={() => setCardToDelete(card)}
                                onDragStart={handleCardDragStart}
                                onDragEnd={handleCardDragEnd}
                                onMoveLeft={
                                    index > 0
                                        ? () => moveCard(card, index - 1)
                                        : null
                                }
                                onMoveRight={
                                    index < project.project_cards.length - 1
                                        ? () => moveCard(card, index + 2)
                                        : null
                                }
                            />

                            {/* Tasks List (Vertical Scroll if too many) */}
                            <div
                                className="flex-1 overflow-y-auto space-y-3 pr-1"
                                onDragOver={(event) => event.preventDefault()}
                                onDrop={(event) =>
                                    handleDropOnColumn(event, card)
                                }
                            >
                                {card.tasks &&
                                    card.tasks.map((task, index) => (
                                        <TaskItem
                                            key={task.id}
                                            task={task}
                                            onEdit={() => setTaskToEdit(task)}
                                            onDelete={() =>
                                                setTaskToDelete(task)
                                            }
                                            isDragging={
                                                draggedTask?.id === task.id
                                            }
                                            dropBefore={
                                                dropTarget?.cardId === card.id &&
                                                dropTarget?.taskId === task.id &&
                                                !dropTarget.after
                                            }
                                            dropAfter={
                                                dropTarget?.cardId === card.id &&
                                                dropTarget?.taskId === task.id &&
                                                dropTarget.after
                                            }
                                            onDragStart={(event) =>
                                                handleDragStart(event, task)
                                            }
                                            onDragEnd={handleDragEnd}
                                            onDragOver={(event) =>
                                                handleTaskDragOver(
                                                    event,
                                                    card,
                                                    task
                                                )
                                            }
                                            onDrop={(event) =>
                                                handleDropOnTask(
                                                    event,
                                                    card,
                                                    index
                                                )
                                            }
                                        />
                                    ))}
                                {(!card.tasks || card.tasks.length === 0) && (
                                    <div className="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg">
                                        <p className="text-xs text-gray-400">
                                            No tasks yet
                                        </p>
                                    </div>
                                )}
                            </div>

                            {/* Add Task */}
                            <TaskForm card={card} />
                        </div>
                    ))}

                <div className="w-80 flex-shrink-0">
                    {!isCreatingCard ? (
                        /* STATE A: Just the clean button */
                        <button
                            onClick={() => setIsCreatingCard(true)}
                            className="w-full bg-white/60 hover:bg-white border-2 border-dashed border-gray-300 hover:border-gray-400 rounded-xl p-4 text-gray-600 font-medium text-sm flex items-center justify-center transition shadow-sm"
                        >
                            + New Column
                        </button>
                    ) : (
                        /* STATE B: The popup/slide-down form */
                        <div className="bg-white rounded-xl p-4 shadow-md border border-gray-200">
                            <form
                                onSubmit={handleCreateCard}
                                className="space-y-3"
                            >
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData("name", e.target.value)
                                    }
                                    placeholder="Enter column name..."
                                    autoFocus // Automatically focuses the input when opened
                                    className="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-2"
                                />
                                {errors.name && (
                                    <div className="text-red-500 text-xs">
                                        {errors.name}
                                    </div>
                                )}

                                <div className="flex items-center space-x-2">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-2 rounded-md transition disabled:opacity-50"
                                    >
                                        Add Column
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            reset();
                                            setIsCreatingCard(false);
                                        }}
                                        className="text-gray-400 hover:text-gray-600 p-1 rounded transition"
                                    >
                                        {/* X Close Icon */}
                                        <svg
                                            className="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}
                </div>
            </main>

            <ConfirmDialog
                show={!!cardToDelete}
                title="Delete column?"
                message={
                    cardToDelete
                        ? `"${cardToDelete.name}" and its ${
                              cardToDelete.tasks?.length ?? 0
                          } ${
                              (cardToDelete.tasks?.length ?? 0) === 1
                                  ? "task"
                                  : "tasks"
                          } will be permanently deleted.`
                        : ""
                }
                confirmLabel="Delete column"
                processing={isDeletingCard}
                onConfirm={deleteCard}
                onClose={() => setCardToDelete(null)}
            />

            <ConfirmDialog
                show={!!taskToDelete}
                title="Delete task?"
                message={
                    taskToDelete
                        ? `"${taskToDelete.name}" will be permanently deleted.`
                        : ""
                }
                confirmLabel="Delete task"
                processing={isDeletingTask}
                onConfirm={deleteTask}
                onClose={() => setTaskToDelete(null)}
            />

            {taskToEdit && (
                <TaskDetailsModal
                    task={taskToEdit}
                    onClose={() => setTaskToEdit(null)}
                />
            )}
        </div>
    );
}
