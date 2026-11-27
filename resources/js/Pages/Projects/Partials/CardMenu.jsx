import Dropdown from "@/Components/Dropdown";

export default function CardMenu({
    onRename,
    onDelete,
    onMoveLeft,
    onMoveRight,
}) {
    return (
        <Dropdown>
            <Dropdown.Trigger>
                <button
                    type="button"
                    aria-label="Card options"
                    className="text-black hover:text-gray-600 p-1 rounded transition"
                >
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
                            d="M5 12h.01M12 12h.01M19 12h.01"
                        />
                    </svg>
                </button>
            </Dropdown.Trigger>

            <Dropdown.Content width="48">
                {onMoveLeft && (
                    <Dropdown.Item
                        onClick={onMoveLeft}
                        className="text-gray-700"
                    >
                        &larr; Move column left
                    </Dropdown.Item>
                )}
                {onMoveRight && (
                    <Dropdown.Item
                        onClick={onMoveRight}
                        className="text-gray-700"
                    >
                        Move column right &rarr;
                    </Dropdown.Item>
                )}
                <Dropdown.Item onClick={onRename} className="text-gray-700">
                    Rename card
                </Dropdown.Item>
                <Dropdown.Item
                    onClick={onDelete}
                    className="text-red-600 hover:bg-red-50 data-[focus]:!bg-red-50"
                >
                    Delete card
                </Dropdown.Item>
            </Dropdown.Content>
        </Dropdown>
    );
}
