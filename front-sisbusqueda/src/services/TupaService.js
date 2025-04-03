import { api } from "src/boot/axios";

class TupaService {
    static async getData(params) {
        return (await api.get('/api/tupas',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/tupas/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/tupas/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/tupas", reg)).data;
        } else {
            return (await api.put(`/api/tupas/${reg.id}`, reg)).data;
        }
    }
}

export default TupaService;
