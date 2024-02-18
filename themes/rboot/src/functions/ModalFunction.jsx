// FOR MODAL
const onClickHandler = (e, fn) => {
  e.preventDefault()
  if (e.target.attributes.r_name) {
    const name = e.target.attributes.r_name.value;
    const type = e.target.attributes.r_type.value;
    const key = e.target.attributes.r_key.value;
    const value = e.target.attributes.r_value.value;
    const alt = e.target.attributes.r_subval && e.target.attributes.r_subval.value;
    const attrs = {
      rkey: key,
      type: type,
      name: name,
      value: value,
      alt: alt
    }
    fn.setModalAttr(attrs)
  }
  fn.setModalOpen(fn.isModalOpen ? false : true)
};
// FOR MODAL
const onCloseHandler = (e, fn) => {
  e.preventDefault()
  fn.setModalOpen(fn.isModalOpen ? false : true)
}
// FOR MODAL
const onSaveHandler = (e, fn) => {

console.log(fn)

  e.preventDefault()
  const modalValue = e.target.attributes.r_data.value;
  const modalSubValue = e.target.attributes.r_subval.value;
  const modalType = e.target.attributes.r_type.value;
  const modalKey = e.target.attributes.r_key.value;
  updateState(modalKey, modalValue,modalSubValue, fn);
  fn.setModalOpen(fn.isModalOpen ? false : true)
}
// FOR MODAL
const updateState = (key, value, subValue, fn) => {
  fn.setData((prevData) => {

    if(key === `img`) {
      prevData[key]['url'] = value;
      prevData[key]['alt'] = subValue;
    }
    else if (key === `link`) {
      prevData[key]['url'] = value;
      prevData[key]['name'] = subValue;
    }
    else {
      prevData[key] = value
    }
    return prevData
  });
}


export default {
  onClickHandler,
  onCloseHandler,
  onSaveHandler
};
