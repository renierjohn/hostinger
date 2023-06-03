import { ComponentBaseData } from '../redux/store';

 const data =
  {
    'id': 13,
    'key': `card_group`,
    'type': `paragraph`,
    'title': 'Equal height Bootstrap 5 cards example',
    'subtitle': 'Lorem ipsum subtitle',
    'list':
      [
        {
          'id': 20,
          'key': `card_group_simple`
        },
        {
          'id': 21,
          'key': `card_group_simple`
        },
        {
          'id': 22,
          'key': `card_group_simple`
        }
      ]
  }

export const CardGroupData = ComponentBaseData(data);

export default {
    CardGroupData,
}
