import { api } from "src/boot/axios";

class EscrituraService {
  static async getData(params) {
    return (await api.get("/api/escrituras", { params })).data;
  }

  static async get(id) {
    return (await api.get(`/api/escrituras/${id}`)).data;
  }

  static async delete(id) {
    return await api.delete(`/api/escrituras/${id}`);
  }



  static async save(reg) {
    const formData = new FormData();
    const isUpdate = reg.id !== undefined;

    // Incluir el ID en FormData si está presente
    if (isUpdate) {
      formData.append('_method', 'PUT'); // Para soportar envíos PUT via FormData
      formData.append('id', reg.id);
    }

    // Agregar todos los campos al FormData
    for (const key in reg) {
      if (key === "file" && reg[key] instanceof File) {
        if (reg[key].type !== "application/pdf") {
          throw new Error("El archivo debe ser un PDF.");
        }
        formData.append(key, reg[key]);
      } else if (Array.isArray(reg[key])) {
        reg[key].forEach(item => formData.append(`${key}[]`, item));
      } else if (key !== "id") { // Evitar duplicar el ID
        formData.append(key, reg[key]);
      }
    }

    // Configurar URL y método
    const url = isUpdate ? `/api/escrituras/${reg.id}` : '/api/escrituras';
    // const method = isUpdate ? 'post' : 'post'; // Usar post con _method=PUT
    const method = 'post';

    // return (await api({
    //   method,
    //   url,
    //   data: formData,
    //   headers: {
    //     "Content-Type": "multipart/form-data",
    //     "X-Requested-With": "XMLHttpRequest"
    //   },
    // })).data;

    // Hacer la petición con axios
    try {
      const response = await api({
        method,
        url,
        data: formData,
        headers: {
          "Content-Type": "multipart/form-data",
          "X-Requested-With": "XMLHttpRequest"
        },
      });

      return response.data; // Retornar los datos recibidos
    } catch (error) {
      console.error("Error al guardar la escritura:", error);
      throw error; // Lanza el error para ser manejado en el componente
    }
  }

  // static async save(reg) {
  //   if (reg.id === undefined || reg.id === null) {
  //     // Si no hay ID, es una solicitud POST (creación)
  //     const formData = new FormData();

  //     // Agregar todos los campos al FormData
  //     for (const key in reg) {
  //       if (key === "file" && reg[key] instanceof File) {
  //         if (reg[key].type !== "application/pdf") {
  //           throw new Error("El archivo debe ser un PDF.");
  //         }
  //         formData.append(key, reg[key]);
  //       } else if (Array.isArray(reg[key])) {
  //         reg[key].forEach(item => formData.append(`${key}[]`, item));
  //       } else if (key !== "id") { // Evitar duplicar el ID
  //         formData.append(key, reg[key]);
  //       }
  //     }

  //     try {
  //       const response = await api.post("/api/escrituras", formData, {
  //         headers: {
  //           "Content-Type": "multipart/form-data",
  //           "X-Requested-With": "XMLHttpRequest"
  //         }
  //       });
  //       return response.data; // Retornar los datos recibidos
  //     } catch (error) {
  //       console.error("Error al guardar la escritura:", error);
  //       throw error; // Lanza el error para ser manejado en el componente
  //     }
  //   } else {
  //     // Si hay ID, es una solicitud PUT (actualización)
  //     const formData = new FormData();
  //     formData.append('_method', 'PUT'); // Para soportar envíos PUT vía FormData
  //     formData.append('id', reg.id);

  //     // Agregar todos los campos al FormData
  //     for (const key in reg) {
  //       if (key === "file" && reg[key] instanceof File) {
  //         if (reg[key].type !== "application/pdf") {
  //           throw new Error("El archivo debe ser un PDF.");
  //         }
  //         formData.append(key, reg[key]);
  //       } else if (Array.isArray(reg[key])) {
  //         reg[key].forEach(item => formData.append(`${key}[]`, item));
  //       } else if (key !== "id") { // Evitar duplicar el ID
  //         formData.append(key, reg[key]);
  //       }
  //     }

  //     try {
  //       const response = await api.put(`/api/escrituras/${reg.id}`, formData, {
  //         headers: {
  //           "Content-Type": "multipart/form-data",
  //           "X-Requested-With": "XMLHttpRequest"
  //         }
  //       });
  //       return response.data; // Retornar los datos recibidos
  //     } catch (error) {
  //       console.error("Error al actualizar la escritura:", error);
  //       throw error; // Lanza el error para ser manejado en el componente
  //     }
  //   }
  // }
}

export default EscrituraService;
