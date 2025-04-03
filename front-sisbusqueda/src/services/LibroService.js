import { api } from "src/boot/axios";

class LibroService {
    static async getData(params) {
        return (await api.get('/api/libros',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/libros/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/libros/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/libros", reg)).data;
        } else {
            return (await api.put(`/api/libros/${reg.id}`, reg)).data;
        }
    }
}

export default LibroService;