import Dropdown from "@/Components/Dropdown";

export default function CardMenu({ onRename }) {
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
                <button
                    type="button"
                    onClick={onRename}
                    className="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100 transition"
                >
                    Rename card
                </button>
            </Dropdown.Content>
        </Dropdown>
    );
}
