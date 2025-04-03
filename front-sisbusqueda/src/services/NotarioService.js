import { api } from "src/boot/axios";

class NotarioService {
    static async getData(params) {
        return (await api.get('/api/notarios',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/notarios/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/notarios/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/notarios", reg)).data;
        } else {
            return (await api.put(`/api/notarios/${reg.id}`, reg)).data;
        }
    }
}

export default NotarioService;
