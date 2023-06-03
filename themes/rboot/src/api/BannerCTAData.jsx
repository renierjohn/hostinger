import { ComponentBaseData } from '../redux/store';

const store = configureStore({
  reducer: Dragstates.reducer
})

const data  =
  {
    'id': 13,
    'key': `banner_cta`,
    'type': `paragraph`,
    'title': 'Equal height Bootstrap 5 cards example',
    'subtitle': 'Lorem ipsum subtitle',
  };

export const BannerCTAData = ComponentBaseData(data);

export default {
    BannerCTAData,
}
