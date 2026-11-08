import { usePage } from "@inertiajs/vue3";
import defaults from "../content-defaults.json";
export function useContent() {
    const page = usePage();
    function text(key) {
        const value = page.props.content?.[key] ?? defaults[key] ?? "";
        // Links and images are data, never executable HTML or JavaScript.
        if (/^\s*(javascript|data|vbscript):/i.test(value)) return "";
        return value;
    }
    return { text };
}
