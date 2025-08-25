import { api } from "src/boot/axios";

class SolicitudService {
  static async getData(params) {
    // return (await api.get("/api/solicitudes", { params })).data;
    return (await api.get("/api/solicitudes", params)).data;
  }

  // static async getData(params = {}) {
  //   return (await api.get("/api/solicitudes", { params: { ...params, rowsPerPage: -1 } })).data;
  // }

  static async get(id) {
    return (await api.get(`/api/solicitudes/${id}`)).data;
  }

  static async update(id, reg) {
    return await api.put(`/api/solicitudes/${id}`, reg);
  }

  static async delete(id) {
    return await api.delete(`/api/solicitudes/${id}`);
  }

  static async save(reg) {
    if (reg.id === undefined || reg.id === null) {
      return (
        await api.post("/api/solicitudes", reg, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
      ).data;
    } else {
      return (await api.put(`/api/solicitudes/${reg.id}`, reg)).data;
    }
  }
}

export default SolicitudService;
