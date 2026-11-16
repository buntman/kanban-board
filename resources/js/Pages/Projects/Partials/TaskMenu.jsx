import Dropdown from "@/Components/Dropdown";

export default function TaskMenu({ onEdit, onDelete }) {
    return (
        <Dropdown>
            <Dropdown.Trigger>
                <button
                    type="button"
                    aria-label="Task options"
                    className="text-gray-400 hover:text-gray-600 p-1 rounded transition"
                >
                    <svg
                        className="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M5 12h.01M12 12h.01M19 12h.01"
                        />
                    </svg>
                </button>
            </Dropdown.Trigger>

            <Dropdown.Content width="48">
                <Dropdown.Item onClick={onEdit} className="text-gray-700">
                    Edit task
                </Dropdown.Item>
                <Dropdown.Item
                    onClick={onDelete}
                    className="text-red-600 hover:bg-red-50 data-[focus]:!bg-red-50"
                >
                    Delete task
                </Dropdown.Item>
            </Dropdown.Content>
        </Dropdown>
    );
}
