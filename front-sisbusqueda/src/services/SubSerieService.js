import { api } from "src/boot/axios";

class SubSerieService {
    static async getData(params) {
        return (await api.get('/api/subseries',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/subseries/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/subseries/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/subseries", reg)).data;
        } else {
            return (await api.put(`/api/subseries/${reg.id}`, reg)).data;
        }
    }
}

export default SubSerieService;
